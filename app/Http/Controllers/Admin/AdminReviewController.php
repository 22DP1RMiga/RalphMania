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
        $query = Review::with(['user', 'product']);

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'pending') {
                $query->where('is_approved', false);
            } elseif ($request->status === 'approved') {
                $query->where('is_approved', true);
            }
        }

        // Filter by rating
        if ($request->has('rating') && $request->rating) {
            $query->where('rating', $request->rating);
        }

        // Sort - pending first, then by date
        $query->orderBy('is_approved', 'asc')
            ->orderBy('created_at', 'desc');

        // Paginate
        $reviews = $query->paginate(20)->through(function ($review) {
            return [
                'id' => $review->id,
                'rating' => $review->rating,
                'title' => $review->title,
                'comment' => $review->comment,
                'is_approved' => $review->is_approved,
                'created_at' => $review->created_at,
                'user' => $review->user ? [
                    'id' => $review->user->id,
                    'username' => $review->user->username,
                    'profile_picture' => $review->user->profile_picture,
                ] : null,
                'product' => $review->product ? [
                    'id' => $review->product->id,
                    'name_lv' => $review->product->name_lv,
                    'slug' => $review->product->slug,
                    'sku' => $review->product->sku,
                    'image' => $review->product->image,
                ] : null,
            ];
        });

        return Inertia::render('Admin/Reviews/Index', [
            'reviews' => $reviews,
            'filters' => $request->only(['status', 'rating']),
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
