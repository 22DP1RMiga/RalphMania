<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CommentController extends Controller
{
    /**
     * Get comments for specific content (API)
     */
    public function byContent(int $contentId): JsonResponse
    {
        $comments = Comment::where('content_id', $contentId)
            ->where('is_approved', true)
            ->with('user:id,username,profile_picture')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($comments);
    }

    /**
     * Store a new comment (WEB - Inertia)
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'content_id' => 'required|exists:content,id',
            'comment_text' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['is_approved'] = false; // Requires admin approval

        Comment::create($validated);

        return redirect()->back()->with('success', 'Komentārs pievienots un gaida apstiprinājumu');
    }

    /**
     * Update a comment
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $comment = Comment::findOrFail($id);

        // Check if user owns the comment
        if ($comment->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'comment_text' => 'required|string|max:1000',
        ]);

        $comment->update($validated);

        return redirect()->back()->with('success', 'Komentārs atjaunināts');
    }

    /**
     * Delete a comment
     */
    public function destroy(int $id): RedirectResponse
    {
        $comment = Comment::findOrFail($id);

        // Check if user owns the comment
        if ($comment->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $comment->delete();

        return redirect()->back()->with('success', 'Komentārs dzēsts');
    }

    /**
     * Admin: List all comments
     */
    public function adminIndex(): Response
    {
        $comments = Comment::with(['user', 'content'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/Comments/Index', [
            'comments' => $comments,
        ]);
    }

    /**
     * Admin: Approve comment
     */
    public function approve(int $id): RedirectResponse
    {
        $comment = Comment::findOrFail($id);
        $comment->update(['is_approved' => true]);

        return redirect()->back()->with('success', 'Komentārs apstiprināts');
    }

    /**
     * Admin: Reject comment
     */
    public function reject(int $id): RedirectResponse
    {
        $comment = Comment::findOrFail($id);
        $comment->delete();

        return redirect()->back()->with('success', 'Komentārs noraidīts');
    }
}
