<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Administrator;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $isSuperAdmin = $user->isSuperAdmin();

        // Get statistics
        $stats = [
            'totalUsers' => User::count(),
            'totalOrders' => Order::count(),
            'totalProducts' => Product::count(),
            // Pareizais kolonnas nosaukums: total_amount, statuss: delivered
            'totalRevenue' => Order::where('status', 'delivered')->sum('total_amount') ?? 0,
            'pendingOrders' => Order::where('status', 'pending')->count(),
            'pendingReviews' => Review::where('is_approved', false)->count(),
            'unreadContacts' => ContactMessage::where('is_read', false)->count(),
            'newUsersToday' => User::whereDate('created_at', today())->count(),
        ];

        // Get recent orders
        $recentOrders = Order::with('user')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'user' => $order->user ? [
                        'username' => $order->user->username,
                        'email' => $order->user->email,
                    ] : null,
                    'customer_name' => $order->customer_name,
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                    'created_at' => $order->created_at,
                ];
            });

        // Data for super admin only
        $administrators = [];
        $allUsers = [];

        if ($isSuperAdmin) {
            $administrators = Administrator::with('user')
                ->orderBy('is_super_admin', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($admin) {
                    return [
                        'id' => $admin->id,
                        'user_id' => $admin->user_id,
                        'full_name' => $admin->full_name,
                        'permissions' => $admin->permissions ?? [],
                        'is_super_admin' => $admin->is_super_admin,
                        'last_login_at' => $admin->last_login_at,
                        'user' => $admin->user ? [
                            'id' => $admin->user->id,
                            'username' => $admin->user->username,
                            'email' => $admin->user->email,
                            'profile_picture' => $admin->user->profile_picture,
                        ] : null,
                    ];
                });

            // Get all users for admin creation
            $allUsers = User::select('id', 'username', 'email')
                ->orderBy('username')
                ->get();
        }

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'administrators' => $administrators,
            'allUsers' => $allUsers,
        ]);
    }

    /**
     * Store a new administrator (Super Admin only).
     */
    public function storeAdministrator(Request $request)
    {
        // Verify super admin
        if (!$request->user()->isSuperAdmin()) {
            abort(403, 'Tikai Super Admin var pievienot administratorus.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'permissions' => 'array',
            'permissions.*' => 'string',
        ], [
            'user_id.required' => 'Lietotājs ir obligāts.',
            'user_id.exists' => 'Izvēlētais lietotājs neeksistē.',
        ]);

        // Check if user is already an administrator
        if (Administrator::where('user_id', $validated['user_id'])->exists()) {
            return back()->withErrors(['user_id' => 'Šis lietotājs jau ir administrators.']);
        }

        $user = User::findOrFail($validated['user_id']);

        // Create administrator record
        Administrator::create([
            'user_id' => $user->id,
            'full_name' => $user->full_name ?? $user->username,
            'permissions' => $validated['permissions'] ?? [],
            'is_super_admin' => false,
        ]);

        // Update user role to administrator
        $adminRole = DB::table('roles')->where('name', 'administrator')->first();
        if ($adminRole) {
            $user->update(['role_id' => $adminRole->id]);
        }

        return back()->with('success', 'Administrators veiksmīgi pievienots!');
    }

    /**
     * Update administrator permissions (Super Admin only).
     */
    public function updatePermissions(Request $request, $id)
    {
        // Verify super admin
        if (!$request->user()->isSuperAdmin()) {
            abort(403, 'Tikai Super Admin var mainīt atļaujas.');
        }

        $admin = Administrator::findOrFail($id);

        // Cannot edit super admin
        if ($admin->is_super_admin) {
            return back()->withErrors(['error' => 'Super Admin atļaujas nevar mainīt.']);
        }

        $validated = $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'string',
        ]);

        $admin->update([
            'permissions' => $validated['permissions'] ?? [],
        ]);

        return back()->with('success', 'Atļaujas veiksmīgi atjauninātas!');
    }

    /**
     * Remove an administrator (Super Admin only).
     */
    public function destroyAdministrator(Request $request, $id)
    {
        // Verify super admin
        if (!$request->user()->isSuperAdmin()) {
            abort(403, 'Tikai Super Admin var noņemt administratorus.');
        }

        $admin = Administrator::findOrFail($id);

        // Cannot remove super admin
        if ($admin->is_super_admin) {
            return back()->withErrors(['error' => 'Super Admin nevar noņemt.']);
        }

        // Reset user role to customer
        $customerRole = DB::table('roles')->where('name', 'customer')->first();
        if ($customerRole && $admin->user) {
            $admin->user->update(['role_id' => $customerRole->id]);
        }

        $admin->delete();

        return back()->with('success', 'Administrators veiksmīgi noņemts!');
    }
}
