<?php

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContentController extends Controller
{
    /**
     * Display a listing of content with pagination
     */
    public function index(Request $request): Response
    {
        $query = Content::where('is_published', true)
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc');

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by video platform
        if ($request->filled('platform')) {
            $query->where('video_platform', $request->platform);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title_lv', 'LIKE', "%{$search}%")
                    ->orWhere('title_en', 'LIKE', "%{$search}%")
                    ->orWhere('description_lv', 'LIKE', "%{$search}%")
                    ->orWhere('description_en', 'LIKE', "%{$search}%");
            });
        }

        $content = $query->paginate(12)->through(fn ($item) => [
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
        ]);

        // Get available categories for filter
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
            ],
        ]);
    }

    /**
     * Display the specified content
     */
    public function show($slug): Response
    {
        $content = Content::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        // Increment view count
        $content->increment('view_count');

        // Check if user has liked
        $userLiked = false;
        if (auth()->check()) {
            $userLiked = $content->likes()
                ->where('user_id', auth()->id())
                ->exists();
        }

        // Get related content (same type, excluding current)
        $relatedContent = Content::where('type', $content->type)
            ->where('id', '!=', $content->id)
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->limit(4)
            ->get(['id', 'title_lv', 'title_en', 'slug', 'thumbnail', 'featured_image', 'type', 'published_at']);

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
     * Toggle like on content
     */
    public function toggleLike($id)
    {
        $content = Content::findOrFail($id);

        $like = $content->likes()
            ->where('user_id', auth()->id())
            ->first();

        if ($like) {
            // Unlike
            $like->delete();
            $content->decrement('like_count');

            return response()->json([
                'liked' => false,
                'like_count' => $content->fresh()->like_count,
            ]);
        } else {
            // Like
            $content->likes()->create([
                'user_id' => auth()->id(),
            ]);
            $content->increment('like_count');

            return response()->json([
                'liked' => true,
                'like_count' => $content->fresh()->like_count,
            ]);
        }
    }

    /**
     * Get featured content for homepage/API
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
     * Get content by type
     */
    public function byType($type): Response
    {
        // Validate type
        $validTypes = ['video', 'blog', 'news', 'announcement'];
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

        // Type labels for page title
        $typeLabels = [
            'video' => ['lv' => 'Video', 'en' => 'Videos'],
            'blog' => ['lv' => 'Blogi', 'en' => 'Blogs'],
            'news' => ['lv' => 'Ziņas', 'en' => 'News'],
            'announcement' => ['lv' => 'Paziņojumi', 'en' => 'Announcements'],
        ];

        return Inertia::render('Content/Index', [
            'content' => $content,
            'filters' => ['type' => $type],
            'pageTitle' => $typeLabels[$type] ?? null,
        ]);
    }

    /**
     * Get latest announcements (for header/sidebar)
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
     * Get latest news
     */
    public function latestNews()
    {
        $news = Content::where('type', 'news')
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->limit(5)
            ->get(['id', 'title_lv', 'title_en', 'slug', 'featured_image', 'description_lv', 'description_en', 'published_at']);

        return response()->json($news);
    }
}
