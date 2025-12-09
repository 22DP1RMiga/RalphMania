<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    /**
     * Get reviews for specific reviewable (API)
     */
    public function byReviewable(string $type, int $id): JsonResponse
    {
        $reviewableType = $type === 'content' ? 'App\\Models\\Content' : 'App\\Models\\Product';

        $reviews = Review::where('reviewable_type', $reviewableType)
            ->where('reviewable_id', $id)
            ->where('is_approved', true)
            ->with('user:id,username,profile_picture')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($reviews);
    }

    /**
     * Store a new review (WEB - Inertia)
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'content_id' => 'nullable|exists:content,id',
            'product_id' => 'nullable|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'nullable|string|max:1000',
        ]);

        // Determine reviewable type
        if (isset($validated['content_id'])) {
            $reviewable_type = 'App\\Models\\Content';
            $reviewable_id = $validated['content_id'];
        } else {
            $reviewable_type = 'App\\Models\\Product';
            $reviewable_id = $validated['product_id'];
        }

        // Get locale
        $locale = app()->getLocale();
        $review_text_field = $locale === 'lv' ? 'review_text_lv' : 'review_text_en';

        // Check if user already reviewed this item
        $existingReview = Review::where('user_id', auth()->id())
            ->where('reviewable_type', $reviewable_type)
            ->where('reviewable_id', $reviewable_id)
            ->first();

        if ($existingReview) {
            // Update existing review
            $existingReview->update([
                'rating' => $validated['rating'],
                $review_text_field => $validated['review_text'] ?? null,
                'is_approved' => false, // Requires re-approval
            ]);

            return redirect()->back()->with('success', 'Atsauksme atjaunināta un gaida apstiprinājumu');
        }

        // Create new review
        Review::create([
            'user_id' => auth()->id(),
            'reviewable_type' => $reviewable_type,
            'reviewable_id' => $reviewable_id,
            'rating' => $validated['rating'],
            $review_text_field => $validated['review_text'] ?? null,
            'is_approved' => false, // Requires admin approval
        ]);

        return redirect()->back()->with('success', 'Atsauksme pievienota un gaida apstiprinājumu');
    }

    /**
     * Update a review
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $review = Review::findOrFail($id);

        // Check if user owns the review
        if ($review->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'nullable|string|max:1000',
        ]);

        $review->update([
            'rating' => $validated['rating'],
            'review_text' => $validated['review_text'] ?? null,
            'is_approved' => false, // Requires re-approval
        ]);

        return redirect()->back()->with('success', 'Atsauksme atjaunināta');
    }

    /**
     * Delete a review
     */
    public function destroy(int $id): RedirectResponse
    {
        $review = Review::findOrFail($id);

        // Check if user owns the review
        if ($review->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $review->delete();

        return redirect()->back()->with('success', 'Atsauksme dzēsta');
    }

    /**
     * Admin: List all reviews
     */
    public function adminIndex(): Response
    {
        $reviews = Review::with(['user', 'reviewable'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/Reviews/Index', [
            'reviews' => $reviews,
        ]);
    }

    /**
     * Admin: Approve review
     */
    public function approve(int $id): RedirectResponse
    {
        $review = Review::findOrFail($id);
        $review->update(['is_approved' => true]);

        return redirect()->back()->with('success', 'Atsauksme apstiprināta');
    }

    /**
     * Admin: Reject review
     */
    public function reject(int $id): RedirectResponse
    {
        $review = Review::findOrFail($id);
        $review->delete();

        return redirect()->back()->with('success', 'Atsauksme noraidīta');
    }
}
