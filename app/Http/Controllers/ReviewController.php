<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Get reviews for reviewable (product or content)
     */
    public function byReviewable($type, $id)
    {
        $reviewableType = $type === 'products' ? 'App\\Models\\Product' : 'App\\Models\\Content';

        $reviews = Review::with('user:id,username')
            ->where('reviewable_type', $reviewableType)
            ->where('reviewable_id', $id)
            ->where('is_approved', true)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($reviews);
    }

    /**
     * Store new review
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reviewable_type' => 'required|in:products,content',
            'reviewable_id' => 'required|integer',
            'rating' => 'required|integer|min:1|max:5',
            'review_text_lv' => 'nullable|string|max:500',
            'review_text_en' => 'nullable|string|max:500',
        ]);

        // Convert type to full class name
        $reviewableType = $validated['reviewable_type'] === 'products'
            ? 'App\\Models\\Product'
            : 'App\\Models\\Content';

        // Check if user already reviewed this item
        $existingReview = Review::where('user_id', $request->user()->id)
            ->where('reviewable_type', $reviewableType)
            ->where('reviewable_id', $validated['reviewable_id'])
            ->first();

        if ($existingReview) {
            return response()->json([
                'message' => 'Jūs jau esat novērtējis šo'
            ], 422);
        }

        $review = Review::create([
            'user_id' => $request->user()->id,
            'reviewable_type' => $reviewableType,
            'reviewable_id' => $validated['reviewable_id'],
            'rating' => $validated['rating'],
            'review_text_lv' => $validated['review_text_lv'] ?? null,
            'review_text_en' => $validated['review_text_en'] ?? null,
            'is_approved' => false, // Needs admin approval
        ]);

        return response()->json([
            'message' => 'Atsauksme pievienota un gaida apstiprinājumu',
            'review' => $review
        ], 201);
    }

    /**
     * Update own review
     */
    public function update(Request $request, $id)
    {
        $review = Review::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'review_text_lv' => 'nullable|string|max:500',
            'review_text_en' => 'nullable|string|max:500',
        ]);

        $review->update($validated);
        $review->update(['is_approved' => false]); // Re-approval needed

        return response()->json([
            'message' => 'Atsauksme atjaunināta',
            'review' => $review
        ]);
    }

    /**
     * Delete own review
     */
    public function destroy(Request $request, $id)
    {
        $review = Review::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $review->delete();

        return response()->json([
            'message' => 'Atsauksme dzēsta'
        ]);
    }

    /**
     * Admin: Approve review
     */
    public function approve($id)
    {
        $review = Review::findOrFail($id);
        $review->update(['is_approved' => true]);

        return response()->json([
            'message' => 'Atsauksme apstiprināta',
            'review' => $review
        ]);
    }

    /**
     * Admin: Reject review
     */
    public function reject($id)
    {
        $review = Review::findOrFail($id);
        $review->delete();

        return response()->json([
            'message' => 'Atsauksme noraidīta'
        ]);
    }
}
