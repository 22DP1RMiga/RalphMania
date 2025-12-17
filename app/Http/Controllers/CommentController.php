<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;

class CommentController extends Controller
{
    public function byContent($contentId): JsonResponse
    {
        try {
            $comments = Comment::where('content_id', $contentId)
                ->where('is_approved', true)
                ->with('user:id,username,first_name,last_name,profile_picture')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($comment) {
                    // veido parādāmo vārdu no vārda + uzvārda vai izmanto lietotājvārdu
                    $displayName = trim($comment->user->first_name . ' ' . $comment->user->last_name);
                    if (empty($displayName)) {
                        $displayName = $comment->user->username;
                    }

                    // ✅ FIX: Correct profile picture path
                    $profilePicture = '/img/default-avatar.png'; // Default

                    if ($comment->user->profile_picture) {
                        // DB stores: "avatars/xxx.png"
                        // We need: "/storage/avatars/xxx.png"
                        $profilePicture = '/storage/' . $comment->user->profile_picture;
                    }

                    return [
                        'id' => $comment->id,
                        'user_id' => $comment->user_id,
                        'comment_text' => $comment->comment_text,
                        'created_at' => $comment->created_at->format('Y-m-d H:i:s'),
                        'user' => [
                            'id' => $comment->user->id,
                            'username' => $comment->user->username,
                            'name' => $displayName,
                            'profile_picture' => $profilePicture,
                        ],
                    ];
                });

            return response()->json($comments);
        } catch (\Exception $e) {
            \Log::error('Error loading comments: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load comments',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * glabā jaunu komentāru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content_id' => 'required|exists:content,id',
            'comment_text' => 'required|string|min:3|max:1000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $comment = Comment::create([
            'user_id' => auth()->id(),
            'content_id' => $validated['content_id'],
            'comment_text' => $validated['comment_text'],
            'parent_id' => $validated['parent_id'] ?? null,
            'is_approved' => true, // automātiskais apstiprināšana
        ]);

        return back()->with('success', 'Comment submitted successfully.');
    }

    /**
     * rediģē eksistējošo komentāru
     */
    public function update(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);

        // Check authorization
        if ($comment->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'comment_text' => 'required|string|min:3|max:1000',
        ]);

        $comment->update([
            'comment_text' => $validated['comment_text'],
        ]);

        return back()->with('success', 'Comment updated successfully.');
    }

    /**
     * izdzēš komentāru
     */
    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);

        // Check authorization
        if ($comment->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $comment->delete();

        return back()->with('success', 'Comment deleted successfully.');
    }

    /**
     * Administrators: Iegūst visus komentārus moderēšanai
     */
    public function adminIndex()
    {
        $comments = Comment::with([
            'user:id,username,first_name,last_name',
            'content:id,title_lv,slug'
        ])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/Comments/Index', [
            'comments' => $comments,
        ]);
    }

    /**
     * Administrators: apstiprina komentāru
     */
    public function approve($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->update(['is_approved' => true]);

        return back()->with('success', 'Comment approved successfully.');
    }

    /**
     * Administrators: noraida (dzēš) komentāru
     */
    public function reject($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->delete();

        return back()->with('success', 'Comment rejected and deleted.');
    }
}
