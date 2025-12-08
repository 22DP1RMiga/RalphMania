<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Get comments for content
     */
    public function byContent($id)
    {
        $comments = Comment::with('user:id,username')
            ->where('content_id', $id)
            ->where('is_approved', true)
            ->whereNull('parent_id') // Only top-level comments
            ->with(['replies' => function($query) {
                $query->where('is_approved', true)
                    ->with('user:id,username');
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($comments);
    }

    /**
     * Store new comment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content_id' => 'required|exists:content,id',
            'comment_text' => 'required|string|max:300',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $comment = Comment::create([
            'user_id' => $request->user()->id,
            'content_id' => $validated['content_id'],
            'comment_text' => $validated['comment_text'],
            'parent_id' => $validated['parent_id'] ?? null,
            'is_approved' => false, // Needs approval
        ]);

        return response()->json([
            'message' => 'Komentārs pievienots un gaida apstiprinājumu',
            'comment' => $comment
        ], 201);
    }

    /**
     * Update own comment
     */
    public function update(Request $request, $id)
    {
        $comment = Comment::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'comment_text' => 'required|string|max:300',
        ]);

        $comment->update($validated);
        $comment->update(['is_approved' => false]); // Re-approval needed

        return response()->json([
            'message' => 'Komentārs atjaunināts',
            'comment' => $comment
        ]);
    }

    /**
     * Delete own comment
     */
    public function destroy(Request $request, $id)
    {
        $comment = Comment::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $comment->delete();

        return response()->json([
            'message' => 'Komentārs dzēsts'
        ]);
    }

    /**
     * Admin: Approve comment
     */
    public function approve($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->update(['is_approved' => true]);

        return response()->json([
            'message' => 'Komentārs apstiprināts',
            'comment' => $comment
        ]);
    }

    /**
     * Admin: Reject comment
     */
    public function reject($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->delete();

        return response()->json([
            'message' => 'Komentārs noraidīts'
        ]);
    }
}
