<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use App\Models\CourierAssignment;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CourierController extends Controller
{
    /**
     * Get the authenticated courier's record.
     * Aborts 403 if no courier record found.
     */
    private function getCourier(): Courier
    {
        $courier = Courier::where('user_id', auth()->id())
            ->where('is_active', true)
            ->first();

        if (!$courier) {
            abort(403, 'Kurjera profils nav atrasts.');
        }

        return $courier;
    }

    // ─── DASHBOARD ────────────────────────────────────────────────────────────

    /**
     * Courier dashboard with stats and active orders.
     * GET /courier/dashboard
     */
    public function dashboard(): Response
    {
        $courier = $this->getCourier();

        // Active assignments (not yet delivered)
        $activeAssignments = CourierAssignment::with(['order.items.product', 'order.payment'])
            ->where('courier_id', $courier->id)
            ->whereNull('completed_at')
            ->whereHas('order', fn($q) => $q->whereNotIn('status', ['delivered', 'cancelled', 'refunded']))
            ->orderBy('assigned_at', 'asc')
            ->get();

        // Stats
        $totalAssigned  = CourierAssignment::where('courier_id', $courier->id)->count();
        $totalCompleted = CourierAssignment::where('courier_id', $courier->id)->whereNotNull('completed_at')->count();
        $totalActive    = $activeAssignments->count();

        // Recent completed (last 10)
        $recentCompleted = CourierAssignment::with(['order'])
            ->where('courier_id', $courier->id)
            ->whereNotNull('completed_at')
            ->orderBy('completed_at', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('Courier/Dashboard', [
            'courier'         => $courier,
            'activeOrders'    => $activeAssignments->map(fn($a) => $this->formatAssignment($a)),
            'recentCompleted' => $recentCompleted->map(fn($a) => $this->formatAssignment($a)),
            'stats' => [
                'totalAssigned'  => $totalAssigned,
                'totalCompleted' => $totalCompleted,
                'totalActive'    => $totalActive,
                'completionRate' => $totalAssigned > 0
                    ? round(($totalCompleted / $totalAssigned) * 100)
                    : 0,
            ],
        ]);
    }

    // ─── ORDERS LIST ─────────────────────────────────────────────────────────

    /**
     * All orders assigned to this courier with filters.
     * GET /courier/orders
     */
    public function orders(Request $request): Response
    {
        $courier = $this->getCourier();

        $query = CourierAssignment::with(['order.items.product', 'order.payment'])
            ->where('courier_id', $courier->id);

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereNull('completed_at');
            } elseif ($request->status === 'completed') {
                $query->whereNotNull('completed_at');
            }
        }

        // Search by order number
        if ($request->filled('search')) {
            $query->whereHas('order', function ($q) use ($request) {
                $q->where('order_number', 'like', '%' . $request->search . '%')
                    ->orWhere('customer_name', 'like', '%' . $request->search . '%');
            });
        }

        $assignments = $query->orderBy('assigned_at', 'desc')->paginate(15);

        return Inertia::render('Courier/Orders', [
            'courier'     => $courier,
            'assignments' => $assignments->through(fn($a) => $this->formatAssignment($a)),
            'filters'     => $request->only(['status', 'search']),
        ]);
    }

    // ─── ORDER DETAIL ─────────────────────────────────────────────────────────

    /**
     * Single order details.
     * GET /courier/orders/{orderId}
     */
    public function showOrder(int $orderId): Response
    {
        $courier = $this->getCourier();

        $assignment = CourierAssignment::with(['order.items.product', 'order.payment'])
            ->where('courier_id', $courier->id)
            ->where('order_id', $orderId)
            ->firstOrFail();

        return Inertia::render('Courier/OrderShow', [
            'courier'    => $courier,
            'assignment' => $this->formatAssignment($assignment),
            'order'      => $this->formatOrder($assignment->order),
        ]);
    }

    // ─── UPDATE ORDER STATUS ─────────────────────────────────────────────────

    /**
     * Courier can update order status within allowed transitions.
     * PUT /courier/orders/{orderId}/status
     *
     * Allowed transitions for couriers:
     *   packed → shipped
     *   shipped → in_transit
     *   in_transit → delivered
     */
    public function updateStatus(Request $request, int $orderId): JsonResponse
    {
        $courier = $this->getCourier();

        $assignment = CourierAssignment::with('order')
            ->where('courier_id', $courier->id)
            ->where('order_id', $orderId)
            ->firstOrFail();

        $order = $assignment->order;

        $validated = $request->validate([
            'status' => 'required|in:shipped,in_transit,delivered',
            'notes'  => 'nullable|string|max:500',
        ]);

        $newStatus = $validated['status'];

        // Validate allowed transitions
        $allowedTransitions = [
            'packed'     => ['shipped'],
            'shipped'    => ['in_transit'],
            'in_transit' => ['delivered'],
        ];

        if (!isset($allowedTransitions[$order->status]) ||
            !in_array($newStatus, $allowedTransitions[$order->status])) {
            return response()->json([
                'success' => false,
                'message' => "Nevar mainīt statusu no '{$order->status}' uz '{$newStatus}'.",
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Update order status
            $updateData = ['status' => $newStatus];

            if ($newStatus === 'shipped') {
                $updateData['shipped_at'] = now();
            } elseif ($newStatus === 'delivered') {
                $updateData['delivered_at'] = now();
            }

            $order->update($updateData);

            // Update assignment notes if provided
            if (!empty($validated['notes'])) {
                $assignment->update(['notes' => $validated['notes']]);
            }

            // Mark assignment as completed when delivered
            if ($newStatus === 'delivered') {
                $assignment->update(['completed_at' => now()]);
            }

            DB::commit();

            return response()->json([
                'success'    => true,
                'message'    => 'Statuss veiksmīgi atjaunināts!',
                'new_status' => $newStatus,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Courier status update failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Kļūda atjauninot statusu.'], 500);
        }
    }

    // ─── ADD/UPDATE NOTES ─────────────────────────────────────────────────────

    /**
     * Save courier notes for an assignment.
     * PUT /courier/orders/{orderId}/notes
     */
    public function updateNotes(Request $request, int $orderId): JsonResponse
    {
        $courier = $this->getCourier();

        $assignment = CourierAssignment::where('courier_id', $courier->id)
            ->where('order_id', $orderId)
            ->firstOrFail();

        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $assignment->update(['notes' => $validated['notes']]);

        return response()->json([
            'success' => true,
            'message' => 'Piezīmes saglabātas!',
        ]);
    }

    // ─── COURIER PROFILE ─────────────────────────────────────────────────────

    /**
     * Courier's own profile page.
     * GET /courier/profile
     */
    public function profile(): Response
    {
        $courier = $this->getCourier();
        $courier->load('user');

        return Inertia::render('Courier/Profile', [
            'courier' => $courier,
        ]);
    }

    // ─── PRIVATE HELPERS ─────────────────────────────────────────────────────

    private function formatAssignment(CourierAssignment $assignment): array
    {
        return [
            'id'           => $assignment->id,
            'assigned_at'  => $assignment->assigned_at,
            'completed_at' => $assignment->completed_at,
            'notes'        => $assignment->notes,
            'is_completed' => $assignment->is_completed,
            'days_active'  => $assignment->days_active,
            'order'        => $assignment->order ? $this->formatOrder($assignment->order) : null,
        ];
    }

    private function formatOrder(Order $order): array
    {
        return [
            'id'             => $order->id,
            'order_number'   => $order->order_number,
            'status'         => $order->status,
            'customer_name'  => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'customer_email' => $order->customer_email,
            'delivery_address'     => $order->delivery_address,
            'delivery_city'        => $order->delivery_city,
            'delivery_postal_code' => $order->delivery_postal_code,
            'delivery_country'     => $order->delivery_country,
            'total_amount'   => $order->total_amount,
            'subtotal'       => $order->subtotal,
            'shipping_cost'  => $order->shipping_cost,
            'discount_amount'=> $order->discount_amount,
            'coupon_code'    => $order->coupon_code,
            'notes'          => $order->notes,
            'tracking_number'=> $order->tracking_number,
            'shipped_at'     => $order->shipped_at,
            'delivered_at'   => $order->delivered_at,
            'created_at'     => $order->created_at,
            'items'          => $order->items->map(fn($item) => [
                'id'           => $item->id,
                'product_name' => $item->product_name,
                'quantity'     => $item->quantity,
                'size'         => $item->size,
                'price'        => $item->price,
                'total'        => $item->total,
                'image'        => $item->product?->image,
            ])->toArray(),
        ];
    }
}
