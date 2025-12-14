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

        // Apply filters
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        if ($request->has('platform') && $request->platform) {
            $query->where('video_platform', $request->platform);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title_lv', 'LIKE', "%{$search}%")
                    ->orWhere('title_en', 'LIKE', "%{$search}%")
                    ->orWhere('description_lv', 'LIKE', "%{$search}%")
                    ->orWhere('description_en', 'LIKE', "%{$search}%");
            });
        }

        // Izmanto ->get(), lai dabūtu VISU saturu bez paginācijas
        $content = $query->paginate(100);

        return Inertia::render('Content/Index', [
            'content' => $content,
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

        return Inertia::render('Content/Show', [
            'content' => $content,
            'userLiked' => $userLiked,
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
     * Get featured content for API
     */
    public function featured()
    {
        $content = Content::where('is_featured', true)
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();

        return response()->json($content);
    }
}
