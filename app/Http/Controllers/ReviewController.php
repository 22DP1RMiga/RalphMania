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
     * API: Iegūst atsauksmes konkrētam objektam (content vai product)
     * GET /api/v1/reviews/{type}/{id}
     *
     * Loģika:
     * - Apstiprinātas (is_approved=1) atsauksmes redz visi
     * - Neapstiprinātas (is_approved=0) redz TIKAI pats autors (savējo)
     */
    public function byReviewable(string $type, int $id): JsonResponse
    {
        $reviewableType = match($type) {
            'product' => 'App\\Models\\Product',
            'content' => 'App\\Models\\Content',
            default   => 'App\\Models\\Product',
        };

        // API routes pēc noklusējuma izmanto 'api' guard — bet sesija ir 'web' guard.
        // Mēģina 'web' guard vispirms (Inertia lietotnes sesija), tad noklusēto.
        $currentUserId = auth('web')->id() ?? auth()->id();

        $query = Review::where('reviewable_type', $reviewableType)
            ->where('reviewable_id', $id)
            ->with('user:id,username,profile_picture,role_id', 'user.role:id,name,display_name_lv,display_name_en')
            ->orderBy('created_at', 'desc');

        if ($currentUserId) {
            // Pieteicies lietotājs redz apstiprinātas + savas neapstiprinātas
            $query->where(function ($q) use ($currentUserId) {
                $q->where('is_approved', true)
                    ->orWhere('user_id', $currentUserId);
            });
        } else {
            // Viesis redz tikai apstiprinātas
            $query->where('is_approved', true);
        }

        $reviews = $query->get()->map(fn($r) => [
            'id'             => $r->id,
            'user_id'        => $r->user_id,
            'rating'         => $r->rating,
            'review_text_lv' => $r->review_text_lv,
            'review_text_en' => $r->review_text_en,
            // Fallback: ja nav lv teksta, izmanto en un otrādi
            'review_text'    => $r->review_text_lv ?? $r->review_text_en,
            'is_approved'    => (bool) $r->is_approved,
            'created_at'     => $r->created_at,
            'user'           => $r->user ? [
                'id'              => $r->user->id,
                'username'        => $r->user->username,
                'profile_picture' => $r->user->profile_picture,
                'role'            => $r->user->role ? [
                    'name'            => $r->user->role->name,
                    'display_name_lv' => $r->user->role->display_name_lv,
                    'display_name_en' => $r->user->role->display_name_en,
                ] : null,
            ] : null,
        ]);

        return response()->json($reviews);
    }

    /**
     * WEB: Saglabā jaunu atsauksmi
     * POST /reviews
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'content_id'  => 'nullable|exists:content,id',
            'product_id'  => 'nullable|exists:products,id',
            'rating'      => 'required|integer|min:1|max:5',
            'review_text' => 'nullable|string|max:1000',
        ]);

        if (isset($validated['content_id'])) {
            $reviewable_type = 'App\\Models\\Content';
            $reviewable_id   = $validated['content_id'];
        } else {
            $reviewable_type = 'App\\Models\\Product';
            $reviewable_id   = $validated['product_id'];
        }

        // Saglabā tekstu abos laukos lai nav lokāles problēmu
        $reviewText = $validated['review_text'] ?? null;

        $existing = Review::where('user_id', auth()->id())
            ->where('reviewable_type', $reviewable_type)
            ->where('reviewable_id', $reviewable_id)
            ->first();

        if ($existing) {
            $existing->update([
                'rating'          => $validated['rating'],
                'review_text_lv'  => $reviewText,
                'review_text_en'  => $reviewText,
                'is_approved'     => false,
            ]);
            return redirect()->back()->with('success', 'Atsauksme atjaunināta un gaida apstiprinājumu');
        }

        Review::create([
            'user_id'         => auth()->id(),
            'reviewable_type' => $reviewable_type,
            'reviewable_id'   => $reviewable_id,
            'rating'          => $validated['rating'],
            'review_text_lv'  => $reviewText,
            'review_text_en'  => $reviewText,
            'is_approved'     => false,
        ]);

        // Aktivitātes žurnāls
        $target = isset($validated['content_id']) ? 'saturs ID ' . $reviewable_id : 'produkts ID ' . $reviewable_id;
        \App\Models\ActivityLog::log(
            'review_added',
            'Lietotājs ' . auth()->user()?->username . ' pievienoja atsauksmi (' . $target . ', vērtējums: ' . $validated['rating'] . '/5)',
        );

        return redirect()->back()->with('success', 'Atsauksme pievienota un gaida apstiprinājumu');
    }

    /**
     * WEB: Atjaunina atsauksmi
     * PUT /reviews/{id}
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $review = Review::findOrFail($id);

        if ($review->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'rating'      => 'required|integer|min:1|max:5',
            'review_text' => 'nullable|string|max:1000',
        ]);

        $reviewText = $validated['review_text'] ?? null;

        $review->update([
            'rating'         => $validated['rating'],
            'review_text_lv' => $reviewText,
            'review_text_en' => $reviewText,
            'is_approved'    => false,
        ]);

        return redirect()->back()->with('success', 'Atsauksme atjaunināta');
    }

    /**
     * WEB: Dzēš atsauksmi
     * DELETE /reviews/{id}
     */
    public function destroy(int $id): RedirectResponse
    {
        $review = Review::findOrFail($id);

        if ($review->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $review->delete();
        return redirect()->back()->with('success', 'Atsauksme dzēsta');
    }

    /**
     * Admin: Visu atsauksmju saraksts
     */
    public function adminIndex(): Response
    {
        $reviews = Review::with(['user', 'reviewable'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/Reviews/Index', ['reviews' => $reviews]);
    }

    /**
     * Admin: Apstiprina atsauksmi
     */
    public function approve(int $id): RedirectResponse
    {
        Review::findOrFail($id)->update(['is_approved' => true]);
        return redirect()->back()->with('success', 'Atsauksme apstiprināta');
    }

    /**
     * Admin: Noraida (dzēš) atsauksmi
     */
    public function reject(int $id): RedirectResponse
    {
        Review::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Atsauksme noraidīta');
    }
}
