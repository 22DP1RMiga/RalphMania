<?php
// ================================================================
// DashboardController.php — pievieno newsletterStatus prop
// Faila atrašanās vieta: app/Http/Controllers/DashboardController.php
//
// Pēc esošā koda pievieno $newsletterStatus un nodod to Inertia::render
// ================================================================

// Pievienot use statements augšā:
// use App\Models\NewsletterSubscriber;

// Metodes iekšā (kopā ar pārējiem datiem) pievieno:

/*
    $user = auth()->user();

    $subscriber = NewsletterSubscriber::where('user_id', $user->id)->first();
    $isSubscribed = $subscriber
        && $subscriber->is_active
        && !$subscriber->is_expired;

    $newsletterStatus = [
        'subscribed'      => $isSubscribed,
        'expires_at'      => $subscriber?->subscription_expires_at?->format('d.m.Y'),
        'days_remaining'  => $subscriber?->days_remaining,
        'is_expired'      => $subscriber?->is_expired ?? false,
    ];

    // Un nodod to Inertia::render:
    return Inertia::render('Dashboard', [
        // ... esošie props ...
        'newsletterStatus' => $newsletterStatus,
    ]);
*/

// ================================================================
// PILNS PIEMĒRS (ja tev DashboardController izskatās šādi):
// ================================================================

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use App\Models\Order;
use App\Models\Review;
use App\Models\Comment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Newsletter status
        $subscriber = NewsletterSubscriber::where('user_id', $user->id)->first();
        $isSubscribed = $subscriber
            && $subscriber->is_active
            && !$subscriber->is_expired;

        $newsletterStatus = [
            'subscribed'     => $isSubscribed,
            'expires_at'     => $subscriber?->subscription_expires_at?->format('d.m.Y'),
            'days_remaining' => $subscriber?->days_remaining,
            'is_expired'     => $subscriber?->is_expired ?? false,
        ];

        // Stats
        $stats = [
            'orders'     => Order::where('user_id', $user->id)->count(),
            'reviews'    => Review::where('user_id', $user->id)->count(),
            'comments'  => Review::where('user_id', $user->id)->count(),
            'cart_items' => $user->cartItems()->count(),
        ];

        // Recent data
        $recentOrders = Order::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get(['id', 'order_number', 'status', 'total_amount', 'created_at']);

        $recentReviews = Review::where('user_id', $user->id)
            ->with('reviewable')
            ->latest()
            ->take(5)
            ->get();

        $recentComments = Comment::where('user_id', $user->id)
            ->with('content')
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'stats'             => $stats,
            'recentOrders'      => $recentOrders,
            'recentReviews'     => $recentReviews,
            'recentComments'    => $recentComments,
            'hasAddress'        => (bool) $user->address,
            'newsletterStatus'  => $newsletterStatus, // ← JAUNAIS
        ]);
    }
}
