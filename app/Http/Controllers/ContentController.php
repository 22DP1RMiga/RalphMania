<?php

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ContentController extends Controller
{
    /**
     * Rāda satura sarakstu ar lappušu numerāciju
     */
    public function index(Request $request): Response
    {
        $query = Content::where('is_published', true)
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc');

        // filtrē pēc tipa
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // filtrē pēc kategorijas
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // filtrē pēc video platformas
        if ($request->filled('platform')) {
            $query->where('video_platform', $request->platform);
        }

        // meklēšanai
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title_lv', 'LIKE', "%{$search}%")
                    ->orWhere('title_en', 'LIKE', "%{$search}%")
                    ->orWhere('description_lv', 'LIKE', "%{$search}%")
                    ->orWhere('description_en', 'LIKE', "%{$search}%");
            });
        }

        // kārtošanai jeb sortēšanai
        $sort = $request->get('sort', 'newest');
        if ($sort === 'oldest') {
            $query->orderBy('published_at', 'asc');
        } elseif ($sort === 'most_liked') {
            $query->orderBy('like_count', 'desc');
        } elseif ($sort === 'most_viewed') {
            $query->orderBy('view_count', 'desc');
        } elseif ($sort === 'best_mood') {
            // Subquery pieeja — MySQL strict mode draudzīga
            $query->orderByRaw('(
                SELECT AVG(cm2.score)
                FROM comment_moods cm2
                INNER JOIN comments c2 ON cm2.comment_id = c2.id
                WHERE c2.content_id = content.id
            ) DESC')
                ->orderBy('published_at', 'desc');
        } else {
            $query->orderBy('published_at', 'desc');
        }

        $content = $query->paginate(200)->through(function ($item) {
            // Noskaņojuma dati
            $moodData = DB::table('comment_moods')
                ->join('comments', 'comment_moods.comment_id', '=', 'comments.id')
                ->where('comments.content_id', $item->id)
                ->selectRaw('AVG(comment_moods.score) as avg_score, COUNT(comment_moods.id) as mood_count')
                ->first();

            return [
                'id' => $item->id,
                'title_lv' => $item->title_lv,
                'title_en' => $item->title_en,
                'slug' => $item->slug,
                'type' => $item->type,
                'description_lv' => $item->description_lv,
                'description_en' => $item->description_en,
                'thumbnail' => $item->thumbnail,
                'featured_image' => $item->featured_image,
                'video_platform' => $item->video_platform,
                'duration' => $item->duration,
                'category' => $item->category,
                'view_count' => $item->view_count,
                'like_count' => $item->like_count,
                'is_featured' => $item->is_featured,
                'published_at' => $item->published_at,
                'avg_mood_score' => $moodData->avg_score !== null ? round($moodData->avg_score) : null,
                'mood_count' => (int)($moodData->mood_count ?? 0),
            ];
        });

        // iegūst pieejamās kategorijas filtram
        $categories = Content::where('is_published', true)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        return Inertia::render('Content/Index', [
            'content' => $content,
            'categories' => $categories,
            'filters' => [
                'type' => $request->type,
                'category' => $request->category,
                'platform' => $request->platform,
                'search' => $request->search,
                'sort' => $sort,
            ],
        ]);
    }

    /**
     * parāda norādīto saturu
     */
    public function show($slug): Response
    {
        $content = Content::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        // skatījumu skaita pieaugums (increment view count)
        $content->increment('view_count');

        // pārbauda vai lietotājs ir "nolaikojis" — web guard (Inertia sesija)
        $userLiked = false;
        $userId = auth('web')->id() ?? auth()->id();
        if ($userId) {
            $userLiked = $content->likes()
                ->where('user_id', $userId)
                ->exists();
        }

        // iegūst saistītu saturu (tāda paša veida, izņemot pašreizējo)
        $relatedContent = Content::where('type', $content->type)
            ->where('id', '!=', $content->id)
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->limit(4)
            ->get(['id', 'title_lv', 'title_en', 'slug', 'thumbnail', 'featured_image', 'type', 'video_url', 'video_platform', 'published_at']);

        return Inertia::render('Content/Show', [
            'content' => [
                'id' => $content->id,
                'title_lv' => $content->title_lv,
                'title_en' => $content->title_en,
                'slug' => $content->slug,
                'type' => $content->type,
                'description_lv' => $content->description_lv,
                'description_en' => $content->description_en,
                'content_body_lv' => $content->content_body_lv,
                'content_body_en' => $content->content_body_en,
                'video_url' => $content->video_url,
                'video_platform' => $content->video_platform,
                'thumbnail' => $content->thumbnail,
                'featured_image' => $content->featured_image,
                'blog_images' => $content->blog_images,
                'duration' => $content->duration,
                'category' => $content->category,
                'view_count' => $content->view_count,
                'like_count' => $content->like_count,
                'is_featured' => $content->is_featured,
                'published_at' => $content->published_at,
                'created_at' => $content->created_at,
            ],
            'userLiked' => $userLiked,
            'relatedContent' => $relatedContent,
        ]);
    }

    /**
     * pārslēdz "patīk" vērtējumu uz saturu
     */
    public function toggleLike($id)
    {
        $content = Content::findOrFail($id);

        $like = $content->likes()
            ->where('user_id', auth()->id())
            ->first();

        if ($like) {
            $like->delete();
            $content->decrement('like_count');

            \App\Models\ActivityLog::log(
                'content_unliked',
                'Lietotājs ' . auth()->user()?->username . ' noņēma "patīk" (saturs: ' . $content->title_lv . ')',
            );

            return response()->json([
                'liked' => false,
                'like_count' => $content->fresh()->like_count,
            ]);
        } else {
            $content->likes()->create(['user_id' => auth()->id()]);
            $content->increment('like_count');

            \App\Models\ActivityLog::log(
                'content_liked',
                'Lietotājs ' . auth()->user()?->username . ' atzīmēja "patīk" (saturs: ' . $content->title_lv . ')',
            );

            return response()->json([
                'liked' => true,
                'like_count' => $content->fresh()->like_count,
            ]);
        }
    }

    /**
     * iegūst piedāvāto (featured) saturu sākumlapai/API
     */
    public function featured()
    {
        $content = Content::where('is_featured', true)
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->limit(6)
            ->get(['id', 'title_lv', 'title_en', 'slug', 'type', 'thumbnail', 'featured_image', 'description_lv', 'description_en', 'view_count', 'published_at']);

        return response()->json($content);
    }

    /**
     * iegūst saturu pēc veida/tipa
     */
    public function byType($type): Response
    {
        // Validate type
        $validTypes = ['video', 'blog', 'post', 'announcement'];
        if (!in_array($type, $validTypes)) {
            abort(404);
        }

        $content = Content::where('type', $type)
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->paginate(12)
            ->through(fn ($item) => [
                'id' => $item->id,
                'title_lv' => $item->title_lv,
                'title_en' => $item->title_en,
                'slug' => $item->slug,
                'type' => $item->type,
                'description_lv' => $item->description_lv,
                'description_en' => $item->description_en,
                'thumbnail' => $item->thumbnail,
                'featured_image' => $item->featured_image,
                'category' => $item->category,
                'view_count' => $item->view_count,
                'like_count' => $item->like_count,
                'published_at' => $item->published_at,
            ]);

        // satura tipa etiķetes lapas virsraksti
        $typeLabels = [
            'video'        => ['lv' => 'Video',      'en' => 'Videos'],
            'blog'         => ['lv' => 'Blogi',       'en' => 'Blogs'],
            'post'         => ['lv' => 'Ziņas',       'en' => 'Posts'],
            'announcement' => ['lv' => 'Paziņojumi',  'en' => 'Announcements'],
        ];

        return Inertia::render('Content/Index', [
            'content'   => $content,
            'filters'   => ['type' => $type],
            'pageTitle' => $typeLabels[$type] ?? null,
        ]);
    }

    /**
     * saņem jaunākos paziņojumus (galvenē/sānjoslā jeb header/sidebar)
     */
    public function latestAnnouncements()
    {
        $announcements = Content::where('type', 'announcement')
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get(['id', 'title_lv', 'title_en', 'slug', 'description_lv', 'description_en', 'published_at']);

        return response()->json($announcements);
    }

    /**
     * saņem jaunākās ziņas
     */
    public function latestNews()
    {
        $news = Content::where('type', 'post')
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->limit(5)
            ->get(['id', 'title_lv', 'title_en', 'slug', 'featured_image', 'description_lv', 'description_en', 'published_at']);

        return response()->json($news);
    }
}
