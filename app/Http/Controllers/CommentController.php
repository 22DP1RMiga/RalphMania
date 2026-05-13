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
            $currentUserId = auth()->id();

            $comments = Comment::where('content_id', $contentId)
                ->where('is_approved', true)
                ->whereNull('parent_id')
                // Paslēpj komentārus no privātiem kontiem (is_public = 0),
                // izņemot, ja tas ir pašreizējā lietotāja komentārs
                ->whereHas('user', function ($q) use ($currentUserId) {
                    $q->where('is_public', true)
                        ->orWhere('id', $currentUserId);
                })
                ->with([
                    'user:id,username,first_name,last_name,profile_picture,role_id,is_public',
                    'user.role:id,name,display_name_lv,display_name_en',
                    'replies' => function ($q) use ($currentUserId) {
                        $q->where('is_approved', true)
                            ->whereHas('user', function ($uq) use ($currentUserId) {
                                $uq->where('is_public', true)
                                    ->orWhere('id', $currentUserId);
                            })
                            ->with([
                                'user:id,username,first_name,last_name,profile_picture,role_id,is_public',
                                'user.role:id,name,display_name_lv,display_name_en',
                            ])
                            ->orderBy('created_at', 'asc');
                    },
                ])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn ($c) => $this->formatComment($c, true));

            return response()->json($comments);
        } catch (\Exception $e) {
            \Log::error('Error loading comments: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load comments'], 500);
        }
    }

    /**
     * Formatē komentāru ar lietotāja datiem un atbildēm
     */
    private function formatComment(Comment $comment, bool $withReplies = false): array
    {
        $pic = '/img/default-avatar.png';
        if ($comment->user?->profile_picture) {
            $pic = '/storage/' . $comment->user->profile_picture;
        }

        $role = null;
        if ($comment->user?->role) {
            $role = [
                'name'            => $comment->user->role->name,
                'display_name_lv' => $comment->user->role->display_name_lv,
                'display_name_en' => $comment->user->role->display_name_en,
            ];
        }

        // Vidējais noskaņojums no comment_moods tabulas
        $avgMood  = \App\Models\CommentMood::where('comment_id', $comment->id)->avg('score');
        $moodCount = \App\Models\CommentMood::where('comment_id', $comment->id)->count();

        // Pašreizējā lietotāja vērtējums, ja pieslēdzies)
        $myMood = null;
        if (auth()->check()) {
            $myMood = \App\Models\CommentMood::where('comment_id', $comment->id)
                ->where('user_id', auth()->id())
                ->value('score');
        }

        $data = [
            'id'             => $comment->id,
            'user_id'        => $comment->user_id,
            'parent_id'      => $comment->parent_id,
            'comment_text'   => $comment->comment_text,
            'avg_mood_score' => $avgMood !== null ? round($avgMood) : null,
            'mood_count'     => $moodCount,
            'my_mood_score'  => $myMood,
            'created_at'     => $comment->created_at->format('Y-m-d H:i:s'),
            'user'         => $comment->user ? [
                'id'              => $comment->user->id,
                'username'        => $comment->user->username,
                'profile_picture' => $pic,
                'role'            => $role,
            ] : null,
        ];

        if ($withReplies) {
            $data['replies'] = $comment->replies
                ->map(fn ($r) => $this->formatComment($r, false))
                ->values()
                ->toArray();
            $data['replies_count'] = count($data['replies']);
        }

        return $data;
    }

    /**
     * Saglabā jaunu komentāru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content_id'   => 'required|exists:content,id',
            'comment_text' => 'required|string|min:3|max:1000',
            'parent_id'    => 'nullable|exists:comments,id',
        ]);

        $comment = Comment::create([
            'user_id'      => auth()->id(),
            'content_id'   => $validated['content_id'],
            'comment_text' => $validated['comment_text'],
            'parent_id'    => $validated['parent_id'] ?? null,
            'is_approved'  => true,
        ]);

        // Aktivitātes žurnāls
        $isReply = !empty($validated['parent_id']);
        \App\Models\ActivityLog::log(
            $isReply ? 'comment_reply' : 'comment_added',
            $isReply
                ? 'Lietotājs ' . auth()->user()?->username . ' atbildēja uz komentāru (satura ID: ' . $validated['content_id'] . ')'
                : 'Lietotājs ' . auth()->user()?->username . ' pievienoja komentāru (satura ID: ' . $validated['content_id'] . ')',
        );

        if (!$request->header('X-Inertia') && ($request->expectsJson() || $request->ajax())) {
            return response()->json(['success' => true, 'id' => $comment->id]);
        }
        return back()->with('success', 'Comment submitted successfully.');
    }

    /**
     * Rediģē eksistējošo komentāru
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

        if (!$request->header('X-Inertia') && ($request->expectsJson() || $request->ajax())) {
            return response()->json(['success' => true]);
        }
        return back()->with('success', 'Comment updated successfully.');
    }

    /**
     * Izdzēš komentāru
     */
    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);

        // Check authorization
        if ($comment->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $comment->delete();

        if (!request()->header('X-Inertia') && (request()->expectsJson() || request()->ajax())) {
            return response()->json(['success' => true]);
        }
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
     * Saglabā vai atjaunina pašreizējā lietotāja noskaņojuma vērtējumu
     * PATCH /comments/{id}/mood
     * Katrs lietotājs glabā savu vērtējumu comment_moods tabulā.
     */
    public function updateMood(Request $request, $id)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        Comment::findOrFail($id); // 404 ja nav

        $validated = $request->validate([
            'score' => 'required|integer|min:0|max:100',
        ]);

        // Upsert — izveido vai atjaunina esošo ierakstu
        \App\Models\CommentMood::updateOrCreate(
            ['comment_id' => $id, 'user_id' => auth()->id()],
            ['score' => $validated['score']]
        );

        // Atgriež jaunāko vidējo
        $avg   = \App\Models\CommentMood::where('comment_id', $id)->avg('score');
        $count = \App\Models\CommentMood::where('comment_id', $id)->count();

        return response()->json([
            'avg_mood_score' => $avg !== null ? round($avg) : null,
            'mood_count'     => $count,
            'my_mood_score'  => $validated['score'],
        ]);
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
