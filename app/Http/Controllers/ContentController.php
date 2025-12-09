<?php

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ContentController extends Controller
{
    /**
     * Display a listing of content (WEB - Inertia)
     */
    public function index(Request $request): Response
    {
        $query = Content::where('is_published', true)
            ->orderBy('published_at', 'desc');

        // Filter by type if provided
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by category if provided
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Pagination
        $perPage = $request->get('per_page', 12);
        $content = $query->paginate($perPage);

        return Inertia::render('Content/Index', [
            'content' => $content,
            'filters' => [
                'type' => $request->get('type'),
                'category' => $request->get('category'),
            ],
        ]);
    }

    /**
     * Display a listing of content (API - JSON)
     */
    public function apiIndex(Request $request): JsonResponse
    {
        $query = Content::where('is_published', true)
            ->orderBy('published_at', 'desc');

        // Filter by type if provided
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by category if provided
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Pagination
        $perPage = $request->get('per_page', 12);
        $content = $query->paginate($perPage);

        return response()->json($content);
    }

    /**
     * Get featured content (API)
     */
    public function featured(): JsonResponse
    {
        $content = Content::where('is_published', true)
            ->where('is_featured', true) // You might need to add this column
            ->orderBy('published_at', 'desc')
            ->limit(6)
            ->get();

        return response()->json($content);
    }

    /**
     * Display a single content item
     */
    public function show(string $slug): Response
    {
        $content = Content::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        // Increment view count
        $content->increment('view_count');

        return Inertia::render('Content/Show', [
            'content' => $content,
        ]);
    }

    /**
     * Get content by type (video or blog)
     */
    public function byType(string $type): Response
    {
        $content = Content::where('is_published', true)
            ->where('type', $type)
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        return Inertia::render('Content/Index', [
            'content' => $content,
            'type' => $type,
        ]);
    }

    /**
     * Admin: List all content
     */
    public function adminIndex(Request $request): Response
    {
        $query = Content::orderBy('created_at', 'desc');

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title_lv', 'like', "%{$search}%")
                    ->orWhere('title_en', 'like', "%{$search}%");
            });
        }

        $content = $query->paginate(20);

        return Inertia::render('Admin/Content/Index', [
            'content' => $content,
        ]);
    }

    /**
     * Admin: Show create form
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Content/Create');
    }

    /**
     * Admin: Store new content
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title_lv' => 'required|string|max:100',
            'title_en' => 'nullable|string|max:100',
            'type' => 'required|in:video,blog',
            'description_lv' => 'nullable|string',
            'description_en' => 'nullable|string',
            'content_body_lv' => 'nullable|string',
            'content_body_en' => 'nullable|string',
            'video_url' => 'nullable|string|max:500',
            'video_platform' => 'nullable|string|max:50',
            'thumbnail' => 'nullable|string|max:255',
            'duration' => 'nullable|integer',
            'category' => 'nullable|string|max:100',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        // Generate slug from title_lv
        $validated['slug'] = Str::slug($validated['title_lv']);
        $validated['created_by'] = auth()->id();

        $content = Content::create($validated);

        return redirect()->route('admin.content.index')
            ->with('success', 'Content created successfully.');
    }

    /**
     * Admin: Show edit form
     */
    public function edit(int $id): Response
    {
        $content = Content::findOrFail($id);

        return Inertia::render('Admin/Content/Edit', [
            'content' => $content,
        ]);
    }

    /**
     * Admin: Update content
     */
    public function update(Request $request, int $id)
    {
        $content = Content::findOrFail($id);

        $validated = $request->validate([
            'title_lv' => 'required|string|max:100',
            'title_en' => 'nullable|string|max:100',
            'type' => 'required|in:video,blog',
            'description_lv' => 'nullable|string',
            'description_en' => 'nullable|string',
            'content_body_lv' => 'nullable|string',
            'content_body_en' => 'nullable|string',
            'video_url' => 'nullable|string|max:500',
            'video_platform' => 'nullable|string|max:50',
            'thumbnail' => 'nullable|string|max:255',
            'duration' => 'nullable|integer',
            'category' => 'nullable|string|max:100',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        // Update slug if title changed
        if ($validated['title_lv'] !== $content->title_lv) {
            $validated['slug'] = Str::slug($validated['title_lv']);
        }

        $content->update($validated);

        return redirect()->route('admin.content.index')
            ->with('success', 'Content updated successfully.');
    }

    /**
     * Admin: Delete content
     */
    public function destroy(int $id)
    {
        $content = Content::findOrFail($id);
        $content->delete();

        return redirect()->route('admin.content.index')
            ->with('success', 'Content deleted successfully.');
    }

    /**
     * Toggle like for content (API)
     */
    public function toggleLike(Request $request, int $id): JsonResponse
    {
        $content = Content::findOrFail($id);
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check if like already exists
        $like = \DB::table('likes')
            ->where('user_id', $user->id)
            ->where('likeable_type', 'App\\Models\\Content')
            ->where('likeable_id', $content->id)
            ->first();

        if ($like) {
            // Unlike
            \DB::table('likes')
                ->where('user_id', $user->id)
                ->where('likeable_type', 'App\\Models\\Content')
                ->where('likeable_id', $content->id)
                ->delete();

            $content->decrement('like_count');

            return response()->json([
                'success' => true,
                'liked' => false,
                'like_count' => $content->like_count,
            ]);
        } else {
            // Like
            \DB::table('likes')->insert([
                'user_id' => $user->id,
                'likeable_type' => 'App\\Models\\Content',
                'likeable_id' => $content->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $content->increment('like_count');

            return response()->json([
                'success' => true,
                'liked' => true,
                'like_count' => $content->like_count,
            ]);
        }
    }
}
