<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use App\Models\Order;
use App\Models\Review;
use App\Models\Comment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Jaunumu vēstules (newsletter) statuss
        $subscriber = NewsletterSubscriber::where('user_id', $user->id)->first();
        $isSubscribed = $subscriber
            && $subscriber->is_active
            && !$subscriber->is_expired;

        $newsletterStatus = [
            'subscribed'     => $isSubscribed,
            'expires_at'     => $subscriber?->subscription_expires_at?->format('d.m.Y'),
            'days_remaining' => $subscriber?->days_remaining,
            'is_expired'     => $subscriber?->is_expired ?? false,
        ];

        // Statusi
        $stats = [
            'orders'           => Order::where('user_id', $user->id)->count(),
            'reviews'          => Review::where('user_id', $user->id)->count(),
            'comments'         => Comment::where('user_id', $user->id)->whereNull('parent_id')->count(),
            // Manas atbildes - cik reizes esmu atbildējis
            'replies_received' => Comment::where('user_id', $user->id)->whereNotNull('parent_id')->count(),
        ];

        // Jaunākie pasūtījumi
        $recentOrders = Order::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get(['id', 'order_number', 'status', 'total_amount', 'created_at']);

        // Jaunākās atsauksmes - ātra ielāde, pārskatāma ar nepieciešamajiem laukiem,
        // pēc tam normalizē tipa un nosaukuma laukus lietotāja puses versijai
        $recentReviews = Review::where('user_id', $user->id)
            ->with('reviewable')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($review) {
                $reviewable = $review->reviewable;

                // Normalizē morfa tipu uz īsu virkni (“Produkts” vai “Saturs”).
                $shortType = null;
                $nameLv    = null;
                $nameEn    = null;
                $slug      = null;

                if ($reviewable) {
                    // reviewable_type ir pilna klase, piemēram, "App\Models\Product" vai "App\Models\Content"
                    $class = class_basename($reviewable);

                    if ($class === 'Product') {
                        $shortType = 'Product';
                        $nameLv    = $reviewable->name_lv;
                        $nameEn    = $reviewable->name_en;
                        $slug      = '/shop/product/' . $reviewable->slug;
                    } elseif ($class === 'Content') {
                        $shortType = 'Content';
                        $nameLv    = $reviewable->title_lv;
                        $nameEn    = $reviewable->title_en;
                        $slug      = '/content/' . $reviewable->slug;
                    }
                }

                return [
                    'id'             => $review->id,
                    'rating'         => $review->rating,
                    'review_text_lv' => $review->review_text_lv,
                    'review_text_en' => $review->review_text_en,
                    'is_approved'    => $review->is_approved,
                    'created_at'     => $review->created_at,
                    'reviewable'     => $reviewable ? [
                        'type'    => $shortType,   // 'Product' vai 'Content'
                        'name_lv' => $nameLv,
                        'name_en' => $nameEn,
                        'link'    => $slug,
                    ] : null,
                ];
            });

        // Diskusijas kurās piedalījies - galvenie komentāri, kur tu esi autors vai esi atbildējis
        $userId = $user->id;

        // 1) ID saraksts: mani galvenie komentāri
        $myTopIds = Comment::where('user_id', $userId)->whereNull('parent_id')->pluck('id');

        // 2) Galvenie komentāri, uz kuriem esmu atbildējis (citu cilvēku pavedienu)
        $repliedToIds = Comment::where('user_id', $userId)
            ->whereNotNull('parent_id')
            ->pluck('parent_id')
            ->map(fn ($parentId) => Comment::where('id', $parentId)->whereNull('parent_id')->value('id'))
            ->filter()
            ->unique();

        $allTopIds = $myTopIds->merge($repliedToIds)->unique()->values();

        $recentComments = Comment::whereIn('id', $allTopIds)
            ->with([
                'content:id,title_lv,title_en,slug',
                'user:id,username,profile_picture',
                'replies' => function ($q) {
                    $q->where('is_approved', true)
                        ->with(['user:id,username,profile_picture'])
                        ->orderBy('created_at', 'asc');
                },
            ])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($comment) use ($userId) {
                // Mood
                $avgMood   = \App\Models\CommentMood::where('comment_id', $comment->id)->avg('score');
                $moodCount = \App\Models\CommentMood::where('comment_id', $comment->id)->count();
                $myMood    = \App\Models\CommentMood::where('comment_id', $comment->id)
                    ->where('user_id', $userId)->value('score');

                $replies = $comment->replies->map(function ($reply) use ($userId) {
                    $myReplyMood = \App\Models\CommentMood::where('comment_id', $reply->id)
                        ->where('user_id', $userId)->value('score');
                    return [
                        'id'             => $reply->id,
                        'comment_text'   => $reply->comment_text,
                        'created_at'     => $reply->created_at,
                        'my_mood_score'  => $myReplyMood,
                        'user'           => $reply->user ? [
                            'username'        => $reply->user->username,
                            'profile_picture' => $reply->user->profile_picture
                                ? '/storage/' . $reply->user->profile_picture
                                : '/img/default-avatar.png',
                        ] : null,
                    ];
                });

                $isMyComment = $comment->user_id === $userId;

                return [
                    'id'             => $comment->id,
                    'comment_text'   => $comment->comment_text,
                    'created_at'     => $comment->created_at,
                    'is_my_comment'  => $isMyComment, // true = es rakstīju, false = cita cilvēka
                    'author'         => $comment->user ? [
                        'username'        => $comment->user->username,
                        'profile_picture' => $comment->user->profile_picture
                            ? '/storage/' . $comment->user->profile_picture
                            : '/img/default-avatar.png',
                    ] : null,
                    'avg_mood_score' => $avgMood !== null ? round($avgMood) : null,
                    'mood_count'     => $moodCount,
                    'my_mood_score'  => $myMood,
                    'replies'        => $replies,
                    'replies_count'  => $replies->count(),
                    'content'        => $comment->content ? [
                        'id'       => $comment->content->id,
                        'title_lv' => $comment->content->title_lv,
                        'title_en' => $comment->content->title_en,
                        'slug'     => $comment->content->slug,
                    ] : null,
                ];
            });

        return Inertia::render('Dashboard', [
            'stats'            => $stats,
            'recentOrders'     => $recentOrders,
            'recentReviews'    => $recentReviews,
            'recentComments'   => $recentComments,
            'hasAddress'       => (bool) $user->address,
            'newsletterStatus' => $newsletterStatus,
        ]);
    }
}
