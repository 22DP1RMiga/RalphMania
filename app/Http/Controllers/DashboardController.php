<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use App\Models\Cart;
use App\Models\Like;
use App\Models\Comment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display user dashboard with statistics
     *
     * GET /dashboard
     */
    public function index(): Response
    {
        $user = auth()->user();

        // Get real statistics
        $stats = [
            // Orders count
            'orders' => Order::where('user_id', $user->id)->count(),

            // Reviews count (reviews written by user)
            'reviews' => Review::where('user_id', $user->id)->count(),

            // Comments count (reviews written by user)
            'comments' => Comment::where('user_id', $user->id)->count(),

            // Favorites count (content liked by user)
            'favorites' => Like::where('user_id', $user->id)
                ->where('likeable_type', 'App\\Models\\Content')
                ->count(),

            // Cart items count
            'cart_items' => Cart::where('user_id', $user->id)
                    ->withCount('items')
                    ->first()
                    ->items_count ?? 0,
        ];

        // Get recent orders (last 5)
        $recentOrders = Order::where('user_id', $user->id)
            ->with(['items.product', 'payment'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get recent reviews (last 5) - FIXED: use reviewable() instead of product()
        $recentReviews = Review::where('user_id', $user->id)
            ->with('reviewable') // Polymorphic relationship
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($review) {
                // Add helper for getting review text based on locale
                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'review_text_lv' => $review->review_text_lv,
                    'review_text_en' => $review->review_text_en,
                    'created_at' => $review->created_at,
                    // Add reviewable data (could be Product or Content)
                    'reviewable' => $review->reviewable ? [
                        'type' => class_basename($review->reviewable_type),
                        'id' => $review->reviewable->id,
                        'name_lv' => $review->reviewable->name_lv ?? null,
                        'name_en' => $review->reviewable->name_en ?? null,
                        'title_lv' => $review->reviewable->title_lv ?? null,
                        'title_en' => $review->reviewable->title_en ?? null,
                    ] : null,
                ];
            });

        // Get recent comments (last 5) - ONLY for Content
        $recentComments = Comment::where('user_id', $user->id)
            ->with('content') // Direct relationship to content
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'comment_text' => $comment->comment_text,
                    'created_at' => $comment->created_at,
                    // Add content data
                    'content' => $comment->content ? [
                        'id' => $comment->content->id,
                        'title_lv' => $comment->content->title_lv,
                        'title_en' => $comment->content->title_en,
                        'slug' => $comment->content->slug,
                    ] : null,
                ];
            });

        // Get user's address info for quick access
        $hasAddress = !empty($user->address) && !empty($user->city);

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'recentReviews' => $recentReviews,
            'recentComments' => $recentComments,
            'hasAddress' => $hasAddress,
        ]);
    }
}
