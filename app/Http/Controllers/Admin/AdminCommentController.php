<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminCommentController extends Controller
{
    /**
     * Display a listing of comments for admin.
     */
    public function index(Request $request)
    {
        $query = Comment::with(['user', 'content']);

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('comment_text', 'LIKE', "%{$search}%");
        }

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'pending') {
                $query->where('is_approved', false);
            } elseif ($request->status === 'approved') {
                $query->where('is_approved', true);
            }
        }

        // Sort - pending first, then by date
        $query->orderBy('is_approved', 'asc')
            ->orderBy('created_at', 'desc');

        // Paginate
        $comments = $query->paginate(20)->through(function ($comment) {
            return [
                'id' => $comment->id,
                'comment_text' => $comment->comment_text,
                'is_approved' => $comment->is_approved,
                'parent_id' => $comment->parent_id,
                'created_at' => $comment->created_at,
                'user' => $comment->user ? [
                    'id' => $comment->user->id,
                    'username' => $comment->user->username,
                    'profile_picture' => $comment->user->profile_picture,
                ] : null,
                'content' => $comment->content ? [
                    'id' => $comment->content->id,
                    'title_lv' => $comment->content->title_lv,
                    'slug' => $comment->content->slug,
                ] : null,
            ];
        });

        return Inertia::render('Admin/Comments/Index', [
            'comments' => $comments,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Approve a comment.
     */
    public function approve($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->update(['is_approved' => true]);

        return back()->with('success', 'Komentārs veiksmīgi apstiprināts!');
    }

    /**
     * Reject (delete) a comment.
     */
    public function reject($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->delete();

        return back()->with('success', 'Komentārs veiksmīgi noraidīts un dzēsts!');
    }
}
