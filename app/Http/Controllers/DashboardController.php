<?php

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
            'comments'   => Comment::where('user_id', $user->id)->count(),
            'cart_items' => $user->cartItems()->count(),
        ];

        // Recent orders
        $recentOrders = Order::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get(['id', 'order_number', 'status', 'total_amount', 'created_at']);

        // Recent reviews — eager load reviewable with needed fields,
        // then normalize the type and name fields for the frontend
        $recentReviews = Review::where('user_id', $user->id)
            ->with('reviewable')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($review) {
                $reviewable = $review->reviewable;

                // Normalize the morph type to a short string ('Product' or 'Content')
                $shortType = null;
                $nameLv    = null;
                $nameEn    = null;
                $slug      = null;

                if ($reviewable) {
                    // reviewable_type is full class e.g. "App\Models\Product"
                    $class = class_basename($reviewable);

                    if ($class === 'Product') {
                        $shortType = 'Product';
                        $nameLv    = $reviewable->name_lv;
                        $nameEn    = $reviewable->name_en;
                        $slug      = '/shop/product/' . $reviewable->slug;
                    } elseif ($class === 'Content') {
                        $shortType = 'Content';
                        $nameLv    = $reviewable->title_lv;
                        $nameEn    = $reviewable->title_en;
                        $slug      = '/content/' . $reviewable->slug;
                    }
                }

                return [
                    'id'             => $review->id,
                    'rating'         => $review->rating,
                    'review_text_lv' => $review->review_text_lv,
                    'review_text_en' => $review->review_text_en,
                    'is_approved'    => $review->is_approved,
                    'created_at'     => $review->created_at,
                    'reviewable'     => $reviewable ? [
                        'type'    => $shortType,   // 'Product' vai 'Content'
                        'name_lv' => $nameLv,
                        'name_en' => $nameEn,
                        'link'    => $slug,
                    ] : null,
                ];
            });

        // Recent comments with content title
        $recentComments = Comment::where('user_id', $user->id)
            ->with(['content:id,title_lv,title_en,slug'])
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'stats'            => $stats,
            'recentOrders'     => $recentOrders,
            'recentReviews'    => $recentReviews,
            'recentComments'   => $recentComments,
            'hasAddress'       => (bool) $user->address,
            'newsletterStatus' => $newsletterStatus,
        ]);
    }
}
