<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AdminCommentController extends Controller
{
    /**
     * Display a listing of comments for admin.
     */
    public function index(Request $request)
    {
        $query = Comment::with(['user', 'content', 'parent.user']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('comment_text', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('username', 'like', "%{$search}%");
                    })
                    ->orWhereHas('content', function ($contentQuery) use ($search) {
                        $contentQuery->where('title_lv', 'like', "%{$search}%")
                            ->orWhere('title_en', 'like', "%{$search}%");
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

        // Filter by content type
        if ($request->filled('content_type')) {
            $query->whereHas('content', function ($q) use ($request) {
                $q->where('type', $request->content_type);
            });
        }

        // Filter by replies only
        if ($request->filled('replies_only') && $request->replies_only === 'true') {
            $query->whereNotNull('parent_id');
        }

        // Sort - pending first, then by date
        $query->orderBy('is_approved', 'asc')
            ->orderBy('created_at', 'desc');

        // Paginate
        $comments = $query->paginate(15)->through(function ($comment) {
            return [
                'id' => $comment->id,
                'comment_text' => $comment->comment_text,
                'is_approved' => $comment->is_approved,
                'parent_id' => $comment->parent_id,
                'created_at' => $comment->created_at,
                'updated_at' => $comment->updated_at,
                'user' => $comment->user ? [
                    'id' => $comment->user->id,
                    'username' => $comment->user->username,
                    'profile_picture' => $comment->user->profile_picture,
                ] : null,
                'content' => $comment->content ? [
                    'id' => $comment->content->id,
                    'title_lv' => $comment->content->title_lv,
                    'title_en' => $comment->content->title_en,
                    'slug' => $comment->content->slug,
                    'type' => $comment->content->type,
                    'thumbnail' => $comment->content->thumbnail,
                    'featured_image' => $comment->content->featured_image,
                ] : null,
                'parent' => $comment->parent ? [
                    'id' => $comment->parent->id,
                    'comment_text' => Str::limit($comment->parent->comment_text, 50),
                    'user' => $comment->parent->user ? [
                        'username' => $comment->parent->user->username,
                    ] : null,
                ] : null,
            ];
        });

        // Get stats
        $stats = [
            'total' => Comment::count(),
            'pending' => Comment::where('is_approved', false)->count(),
            'approved' => Comment::where('is_approved', true)->count(),
            'replies' => Comment::whereNotNull('parent_id')->count(),
        ];

        return Inertia::render('Admin/Comments/Index', [
            'comments' => $comments,
            'filters' => $request->only(['search', 'status', 'content_type', 'replies_only']),
            'stats' => $stats,
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

        // Also delete all replies to this comment
        Comment::where('parent_id', $id)->delete();

        $comment->delete();

        return back()->with('success', 'Komentārs veiksmīgi noraidīts un dzēsts!');
    }
}
