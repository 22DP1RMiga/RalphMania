<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use App\Models\CourierAssignment;
use App\Models\Order;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminCourierController extends Controller
{
    // ─── INDEX ────────────────────────────────────────────────────────────────

    /**
     * List all couriers.
     * GET /admin/couriers
     */
    public function index(Request $request): Response
    {
        $query = Courier::with('user')
            ->withCount(['assignments', 'activeAssignments', 'completedAssignments']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('delivery_area', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($uq) => $uq->where('email', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $couriers = $query->orderBy('is_active', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->through(fn($c) => $this->formatCourier($c));

        $stats = [
            'total'     => Courier::count(),
            'active'    => Courier::where('is_active', true)->count(),
            'inactive'  => Courier::where('is_active', false)->count(),
            'total_active_deliveries' => CourierAssignment::whereNull('completed_at')->count(),
        ];

        // Users who can be made couriers (no courier record yet, role = user)
        $availableUsers = User::whereDoesntHave('courier')
            ->where('is_active', true)
            ->whereHas('role', fn($q) => $q->whereIn('name', ['user', 'courier']))
            ->orderBy('username')
            ->get(['id', 'username', 'email', 'profile_picture']);

        // Orders ready to be assigned (status = packed, no active courier assignment)
        $assignableOrders = Order::whereIn('status', ['packed', 'shipped', 'in_transit'])
            ->whereDoesntHave('courierAssignments', fn($q) => $q->whereNull('completed_at'))
            ->orderBy('created_at', 'asc')
            ->get(['id', 'order_number', 'customer_name', 'delivery_city', 'status', 'created_at']);

        return Inertia::render('Admin/Couriers/Index', [
            'couriers'        => $couriers,
            'stats'           => $stats,
            'filters'         => $request->only(['search', 'status']),
            'availableUsers'  => $availableUsers,
            'assignableOrders'=> $assignableOrders,
        ]);
    }

    // ─── STORE (CREATE COURIER) ───────────────────────────────────────────────

    /**
     * POST /admin/couriers
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'user_id'       => 'required|exists:users,id|unique:couriers,user_id',
            'full_name'     => 'required|string|max:100',
            'phone'         => 'required|string|max:20',
            'vehicle_type'  => 'nullable|string|max:50',
            'delivery_area' => 'nullable|string|max:100',
            'hired_at'      => 'nullable|date',
        ]);

        $courierRole = Role::where('name', 'courier')->firstOrFail();

        DB::beginTransaction();
        try {
            // Assign courier role to user
            User::findOrFail($validated['user_id'])
                ->update(['role_id' => $courierRole->id]);

            Courier::create(array_merge($validated, ['is_active' => true]));

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Kļūda pievienojot kurjeru: ' . $e->getMessage());
        }

        return back()->with('success', 'Kurjers veiksmīgi pievienots!');
    }

    // ─── UPDATE ───────────────────────────────────────────────────────────────

    /**
     * PUT /admin/couriers/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $courier = Courier::with('user')->findOrFail($id);

        $validated = $request->validate([
            'full_name'     => 'required|string|max:100',
            'phone'         => 'required|string|max:20',
            'vehicle_type'  => 'nullable|string|max:50',
            'delivery_area' => 'nullable|string|max:100',
            'hired_at'      => 'nullable|date',
            'username'      => 'nullable|string|max:50|unique:users,username,' . ($courier->user_id ?? 0),
        ]);

        DB::beginTransaction();
        try {
            $courier->update([
                'full_name'     => $validated['full_name'],
                'phone'         => $validated['phone'],
                'vehicle_type'  => $validated['vehicle_type'] ?? null,
                'delivery_area' => $validated['delivery_area'] ?? null,
                'hired_at'      => $validated['hired_at'] ?? null,
            ]);

            if ($courier->user && !empty($validated['username'])) {
                $courier->user->update(['username' => $validated['username']]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Kļūda saglabājot: ' . $e->getMessage()], 500);
        }

        return response()->json(['success' => true, 'message' => 'Kurjers atjaunināts!']);
    }

    // ─── TOGGLE ACTIVE ────────────────────────────────────────────────────────

    /**
     * PUT /admin/couriers/{id}/toggle-active
     */
    public function toggleActive(int $id): JsonResponse
    {
        $courier = Courier::with('user')->findOrFail($id);

        $newActive = !$courier->is_active;
        $courier->update(['is_active' => $newActive]);

        // Also activate/deactivate the user account accordingly
        $courier->user?->update(['is_active' => $newActive]);

        $status = $newActive ? 'aktivizēts' : 'deaktivizēts';
        return response()->json(['success' => true, 'message' => "Kurjers {$status}!", 'is_active' => $newActive]);
    }

    // ─── DESTROY ─────────────────────────────────────────────────────────────

    /**
     * DELETE /admin/couriers/{id}
     */
    public function destroy(int $id): \Illuminate\Http\RedirectResponse
    {
        $courier = Courier::with('user')->findOrFail($id);

        // Check no active deliveries
        if ($courier->activeAssignments()->count() > 0) {
            return back()->with('error', 'Nevar dzēst kurjeru ar aktīviem piegādes uzdevumiem!');
        }

        DB::beginTransaction();
        try {
            // Demote user back to regular user role
            $userRole = Role::where('name', 'user')->first();
            $courier->user?->update(['role_id' => $userRole?->id ?? 2]);

            $courier->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Kļūda dzēšot kurjeru.');
        }

        return back()->with('success', 'Kurjers noņemts!');
    }

    // ─── ASSIGN ORDER ─────────────────────────────────────────────────────────

    /**
     * Assign an order to a courier.
     * POST /admin/couriers/assign
     */
    public function assignOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'courier_id' => 'required|exists:couriers,id',
            'order_id'   => 'required|exists:orders,id',
            'notes'      => 'nullable|string|max:500',
        ]);

        $courier = Courier::findOrFail($validated['courier_id']);
        $order   = Order::findOrFail($validated['order_id']);

        // Check if order already has active assignment
        $existingActive = CourierAssignment::where('order_id', $order->id)
            ->whereNull('completed_at')
            ->first();

        if ($existingActive) {
            return response()->json([
                'success' => false,
                'message' => 'Šim pasūtījumam jau ir aktīvs kurjera piešķīrums.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            CourierAssignment::create([
                'courier_id'  => $courier->id,
                'order_id'    => $order->id,
                'assigned_at' => now(),
                'notes'       => $validated['notes'] ?? null,
            ]);

            // Auto-advance status from 'packed' to 'shipped' when assigned
            if ($order->status === 'packed') {
                $order->update(['status' => 'shipped', 'shipped_at' => now()]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Kļūda piešķirot pasūtījumu.'], 500);
        }

        return response()->json([
            'success' => true,
            'message' => "Pasūtījums {$order->order_number} piešķirts kurjeram {$courier->full_name}!",
        ]);
    }

    // ─── UNASSIGN ORDER ───────────────────────────────────────────────────────

    /**
     * Remove order assignment (e.g. reassign to different courier).
     * DELETE /admin/couriers/assignments/{assignmentId}
     */
    public function unassignOrder(int $assignmentId): JsonResponse
    {
        $assignment = CourierAssignment::with('order')->findOrFail($assignmentId);

        if ($assignment->completed_at) {
            return response()->json(['success' => false, 'message' => 'Nevar noņemt jau pabeigtu piešķīrumu.'], 422);
        }

        $orderNumber = $assignment->order?->order_number;
        $assignment->delete();

        return response()->json([
            'success' => true,
            'message' => "Piešķīrums pasūtījumam {$orderNumber} noņemts.",
        ]);
    }

    // ─── COURIER SHOW (ADMIN VIEW) ────────────────────────────────────────────

    /**
     * GET /admin/couriers/{id}
     */
    public function show(int $id): Response
    {
        $courier = Courier::with(['user', 'assignments.order.items'])
            ->withCount(['assignments', 'activeAssignments', 'completedAssignments'])
            ->findOrFail($id);

        $recentAssignments = CourierAssignment::with(['order'])
            ->where('courier_id', $id)
            ->orderBy('assigned_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn($a) => [
                'id'           => $a->id,
                'assigned_at'  => $a->assigned_at,
                'completed_at' => $a->completed_at,
                'notes'        => $a->notes,
                'order' => $a->order ? [
                    'id'           => $a->order->id,
                    'order_number' => $a->order->order_number,
                    'status'       => $a->order->status,
                    'customer_name'=> $a->order->customer_name,
                    'total_amount' => $a->order->total_amount,
                    'created_at'   => $a->order->created_at,
                ] : null,
            ]);

        return Inertia::render('Admin/Couriers/Show', [
            'courier'            => $this->formatCourier($courier),
            'recentAssignments'  => $recentAssignments,
        ]);
    }

    // ─── PRIVATE HELPERS ─────────────────────────────────────────────────────

    private function formatCourier(Courier $courier): array
    {
        return [
            'id'                       => $courier->id,
            'user_id'                  => $courier->user_id,
            'full_name'                => $courier->full_name,
            'vehicle_type'             => $courier->vehicle_type,
            'delivery_area'            => $courier->delivery_area,
            'phone'                    => $courier->phone,
            'is_active'                => $courier->is_active,
            'hired_at'                 => $courier->hired_at,
            'created_at'               => $courier->created_at,
            'assignments_count'        => $courier->assignments_count ?? 0,
            'active_assignments_count' => $courier->active_assignments_count ?? 0,
            'completed_assignments_count' => $courier->completed_assignments_count ?? 0,
            'user' => $courier->user ? [
                'id'              => $courier->user->id,
                'username'        => $courier->user->username,
                'email'           => $courier->user->email,
                'profile_picture' => $courier->user->profile_picture,
                'is_active'       => $courier->user->is_active,
            ] : null,
        ];
    }
}
