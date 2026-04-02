<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        // ── ADMIN BADGES ──────────────────────────────────────────────────────
        // Only computed for admin users on admin/* routes to avoid unnecessary
        // DB queries on every public page load.
        // Accessible in Vue as: page.props.adminBadges
        $adminBadges = null;

        if ($user && $request->is('admin/*') && $user->role?->name === 'administrator') {
            $adminBadges = [
                // Unread contact messages (all, including courier reports)
                'contacts' => ContactMessage::where('is_read', false)->count(),

                // Courier problem reports specifically (unread)
                'couriers' => ContactMessage::where('is_read', false)
                    ->where('subject', 'like', '%Kurjers%')
                    ->count(),

                // Orders placed in the last 24 h that are still pending/processing
                'orders'   => Order::whereIn('status', ['pending', 'processing'])
                    ->where('created_at', '>=', now()->subHours(24))
                    ->count(),

                // New user registrations in the last 48 h
                'users'    => User::where('created_at', '>=', now()->subHours(48))->count(),

                // Active products with critically low stock (≤5 units)
                'products' => Product::where('is_active', true)
                    ->where('stock_quantity', '<=', 5)
                    ->count(),
            ];
        }

        return [
            ...parent::share($request),
            'auth' => [
                // Nodod lietotāja datus ar administratora atļaujām
                // Tas ir nepieciešams useAdminPermission.js composable darbībai
                'user' => $user ? array_merge($user->toArray(), [
                    // Pievieno administrator.permissions tieši user objektam
                    // lai useAdminPermission.js var piekļūt: user.administrator.permissions
                    'administrator' => $user->administrator ? [
                        'permissions'   => $user->administrator->permissions ?? [],
                        'is_super_admin'=> $user->administrator->is_super_admin ?? false,
                    ] : null,
                ]) : null,
            ],
            'adminBadges' => $adminBadges,
        ];
    }
}
