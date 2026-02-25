<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use App\Models\CourierAssignment;
use App\Models\ContactMessage;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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
            'subtotal'        => $order->subtotal,
            'shipping_cost'   => $order->shipping_cost,
            'discount_amount' => $order->discount_amount ?? 0,
            'coupon_code'     => $order->coupon_code,
            'total_amount'    => $order->total_amount,
            'notes'           => $order->notes,
            'tracking_number' => $order->tracking_number,
            'shipped_at'      => $order->shipped_at,
            'delivered_at'    => $order->delivered_at,
            'created_at'      => $order->created_at,
            'items'           => $order->items->map(fn($item) => [
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

    /**
     * Courier inbox — sent reports + admin replies.
     * GET /courier/inbox
     */
    public function inbox(): JsonResponse
    {
        $courier = $this->getCourier();
        $courier->load('user');

        $messages = ContactMessage::where(function ($q) use ($courier) {
            $q->where('user_id', $courier->user_id);
            if ($courier->user?->email) {
                $q->orWhere('email', $courier->user->email);
            }
        })
            ->where('subject', 'like', '%Kurjers%')
            ->orderBy('created_at', 'desc')
            ->limit(30)
            ->get()
            ->map(fn($m) => [
                'id'         => $m->id,
                'subject'    => $m->subject,
                'message'    => $m->message,
                'created_at' => $m->created_at,
                'is_read'    => $m->is_read,
                'is_replied' => $m->is_replied,
                'reply_text' => $m->reply_text,
                'replied_at' => $m->replied_at,
            ]);

        return response()->json(['messages' => $messages]);
    }

    /**
     * Courier reports a delivery problem to admin.
     * POST /courier/report
     */
    public function reportProblem(Request $request): JsonResponse
    {
        $courier = $this->getCourier();
        $courier->load('user');

        $validated = $request->validate([
            'problem_type' => 'required|string|in:address,customer,vehicle,package,other',
            'order_id'     => 'nullable|integer|exists:orders,id',
            'description'  => 'required|string|min:10|max:1000',
        ]);

        $typeLabels = [
            'address'  => 'Nepareiza adrese',
            'customer' => 'Klients nesasniegts',
            'vehicle'  => 'Transportlīdzekļa problēma',
            'package'  => 'Bojāts iepakojums',
            'other'    => 'Cita problēma',
        ];

        $typeLabel = $typeLabels[$validated['problem_type']];
        $orderRef  = '';

        if ($validated['order_id']) {
            $order    = Order::find($validated['order_id']);
            $orderRef = $order ? " — Pas. #{$order->order_number}" : '';
        }

        ContactMessage::create([
            'user_id' => $courier->user_id,
            'name'    => $courier->full_name,
            'email'   => $courier->user?->email ?? 'kurjers@ralphmania.lv',
            'phone'   => $courier->phone,
            'subject' => "[🚨 Kurjers] {$typeLabel}{$orderRef}",
            'message' => $validated['description'],
            'is_read' => false,
        ]);

        $adminEmail = config('mail.admin_email', env('ADMIN_EMAIL', 'ralphmania.roltonslv@gmail.com'));

        try {
            Mail::send([], [], function ($mail) use ($courier, $typeLabel, $orderRef, $validated, $adminEmail) {
                $mail->to($adminEmail)
                    ->subject("[🚨 Kurjers] {$typeLabel}{$orderRef}")
                    ->html(
                        "<div style='font-family:sans-serif;max-width:600px;margin:0 auto;padding:24px'>" .
                        "<div style='background:#fee2e2;border:2px solid #fecaca;border-radius:8px;padding:16px;margin-bottom:20px'>" .
                        "<h2 style='color:#991b1b;margin:0 0 4px 0'>🚨 Kurjera problēmas ziņojums</h2>" .
                        "<p style='color:#dc2626;margin:0;font-size:14px'>{$typeLabel}{$orderRef}</p>" .
                        "</div>" .
                        "<table style='width:100%;border-collapse:collapse;margin-bottom:20px'>" .
                        "<tr><td style='padding:8px 0;color:#6b7280;font-size:13px;width:140px'>Kurjers:</td><td style='padding:8px 0;font-weight:600'>{$courier->full_name}</td></tr>" .
                        "<tr><td style='padding:8px 0;color:#6b7280;font-size:13px'>Tālrunis:</td><td style='padding:8px 0'>{$courier->phone}</td></tr>" .
                        "</table>" .
                        "<div style='background:#f9fafb;border-radius:8px;padding:16px;border:1px solid #e5e7eb'>" .
                        "<p style='color:#6b7280;font-size:12px;margin:0 0 8px 0;text-transform:uppercase'>Apraksts</p>" .
                        "<p style='color:#111827;line-height:1.7;margin:0'>" . nl2br(htmlspecialchars($validated['description'])) . "</p>" .
                        "</div></div>"
                    );
            });
        } catch (\Exception $e) {
            \Log::warning('Courier report email failed: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => 'Ziņojums nosūtīts administratoram!']);
    }
}
