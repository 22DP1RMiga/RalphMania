<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminReviewController extends Controller
{
    /**
     * Display a listing of reviews for admin.
     */
    public function index(Request $request)
    {
        $query = Review::with(['user', 'reviewable']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('review_text_lv', 'like', "%{$search}%")
                    ->orWhere('review_text_en', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('username', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query->where('is_approved', false);
            } elseif ($request->status === 'approved') {
                $query->where('is_approved', true);
            }
        }

        // Filter by rating
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        // Filter by type (product or content)
        if ($request->filled('type')) {
            if ($request->type === 'product') {
                $query->where('reviewable_type', 'App\\Models\\Product');
            } elseif ($request->type === 'content') {
                $query->where('reviewable_type', 'App\\Models\\Content');
            }
        }

        // Sort - pending first, then by date
        $query->orderBy('is_approved', 'asc')
            ->orderBy('created_at', 'desc');

        // Paginate
        $reviews = $query->paginate(15)->through(function ($review) {
            // Determine reviewable info based on type
            $reviewableInfo = null;
            $reviewableType = null;

            if ($review->reviewable) {
                if ($review->reviewable_type === 'App\\Models\\Product') {
                    $reviewableType = 'product';
                    $reviewableInfo = [
                        'id' => $review->reviewable->id,
                        'name_lv' => $review->reviewable->name_lv ?? null,
                        'name_en' => $review->reviewable->name_en ?? null,
                        'slug' => $review->reviewable->slug,
                        'sku' => $review->reviewable->sku ?? null,
                        'image' => $review->reviewable->image ?? null,
                    ];
                } elseif ($review->reviewable_type === 'App\\Models\\Content') {
                    $reviewableType = 'content';
                    $reviewableInfo = [
                        'id' => $review->reviewable->id,
                        'title_lv' => $review->reviewable->title_lv ?? null,
                        'title_en' => $review->reviewable->title_en ?? null,
                        'slug' => $review->reviewable->slug,
                        'type' => $review->reviewable->type ?? null,
                        'thumbnail' => $review->reviewable->thumbnail ?? null,
                        'featured_image' => $review->reviewable->featured_image ?? null,
                    ];
                }
            }

            return [
                'id' => $review->id,
                'rating' => $review->rating,
                'review_text_lv' => $review->review_text_lv,
                'review_text_en' => $review->review_text_en,
                'is_approved' => $review->is_approved,
                'created_at' => $review->created_at,
                'updated_at' => $review->updated_at,
                'user' => $review->user ? [
                    'id' => $review->user->id,
                    'username' => $review->user->username,
                    'profile_picture' => $review->user->profile_picture,
                ] : null,
                'reviewable_type' => $reviewableType,
                'reviewable' => $reviewableInfo,
            ];
        });

        // Get stats
        $stats = [
            'total' => Review::count(),
            'pending' => Review::where('is_approved', false)->count(),
            'approved' => Review::where('is_approved', true)->count(),
            'products' => Review::where('reviewable_type', 'App\\Models\\Product')->count(),
            'content' => Review::where('reviewable_type', 'App\\Models\\Content')->count(),
            'average_rating' => round(Review::where('is_approved', true)->avg('rating'), 1) ?? 0,
        ];

        return Inertia::render('Admin/Reviews/Index', [
            'reviews' => $reviews,
            'filters' => $request->only(['search', 'status', 'rating', 'type']),
            'stats' => $stats,
        ]);
    }

    /**
     * Approve a review.
     */
    public function approve($id)
    {
        $review = Review::findOrFail($id);
        $review->update(['is_approved' => true]);

        return back()->with('success', 'Atsauksme veiksmīgi apstiprināta!');
    }

    /**
     * Reject (delete) a review.
     */
    public function reject($id)
    {
        $review = Review::findOrFail($id);
        $review->delete();

        return back()->with('success', 'Atsauksme veiksmīgi noraidīta un dzēsta!');
    }
}
