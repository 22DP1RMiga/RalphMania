<?php

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContentController extends Controller
{
    /**
     * Display content home page (web route)
     */
    public function index()
    {
        return Inertia::render('Content/Index');
    }

    /**
     * Display single content page (web route)
     */
    public function show($slug)
    {
        $content = Content::where('slug', $slug)
            ->where('is_published', true)
            ->with(['creator', 'comments'])
            ->firstOrFail();

        // Increment view count
        $content->increment('view_count');

        return Inertia::render('Content/Show', [
            'content' => $content,
        ]);
    }

    /**
     * Display content by type (web route)
     */
    public function byType($type)
    {
        return Inertia::render('Content/Type', [
            'type' => $type,
        ]);
    }

    /**
     * API: Get all content (paginated)
     */
    public function apiIndex(Request $request)
    {
        $query = Content::where('is_published', true)
            ->with('creator');

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'published_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = $request->get('per_page', 12);
        $content = $query->paginate($perPage);

        return response()->json($content);
    }

    /**
     * API: Get featured content
     */
    public function featured()
    {
        $content = Content::where('is_published', true)
            ->where('is_featured', true)
            ->with('creator')
            ->limit(6)
            ->orderBy('published_at', 'desc')
            ->get();

        return response()->json($content);
    }

    /**
     * API: Get single content by slug
     */
    public function apiShow($slug)
    {
        $content = Content::where('slug', $slug)
            ->where('is_published', true)
            ->with(['creator', 'comments'])
            ->firstOrFail();

        return response()->json($content);
    }

    /**
     * API: Get content by type
     */
    public function apiByType($type)
    {
        $content = Content::where('is_published', true)
            ->where('type', $type)
            ->with('creator')
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        return response()->json($content);
    }

    /**
     * API: Increment view count
     */
    public function incrementView($id)
    {
        $content = Content::findOrFail($id);
        $content->increment('view_count');

        return response()->json([
            'success' => true,
            'view_count' => $content->view_count,
        ]);
    }

    /**
     * ADMIN: Get all content
     */
    public function adminIndex()
    {
        return Inertia::render('Admin/Content/Index');
    }

    /**
     * ADMIN: Show create content form
     */
    public function create()
    {
        return Inertia::render('Admin/Content/Create');
    }

    /**
     * ADMIN: Store new content
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'slug' => 'required|string|max:170|unique:content',
            'description' => 'nullable|string',
            'content_url' => 'required|url|max:255',
            'type' => 'required|in:video,blog',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['published_at'] = $validated['is_published'] ? now() : null;

        $content = Content::create($validated);

        return redirect()->route('admin.content.index')
            ->with('success', 'Content created successfully');
    }

    /**
     * ADMIN: Show edit content form
     */
    public function edit($id)
    {
        $content = Content::findOrFail($id);

        return Inertia::render('Admin/Content/Edit', [
            'content' => $content,
        ]);
    }

    /**
     * ADMIN: Update content
     */
    public function update(Request $request, $id)
    {
        $content = Content::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'slug' => 'required|string|max:170|unique:content,slug,' . $id,
            'description' => 'nullable|string',
            'content_url' => 'required|url|max:255',
            'type' => 'required|in:video,blog',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
        ]);

        if ($validated['is_published'] && !$content->published_at) {
            $validated['published_at'] = now();
        }

        $content->update($validated);

        return redirect()->route('admin.content.index')
            ->with('success', 'Content updated successfully');
    }

    /**
     * ADMIN: Delete content
     */
    public function destroy($id)
    {
        $content = Content::findOrFail($id);
        $content->delete();

        return redirect()->route('admin.content.index')
            ->with('success', 'Content deleted successfully');
    }

    /**
     * ADMIN API: Toggle published status
     */
    public function togglePublished($id)
    {
        $content = Content::findOrFail($id);
        $content->is_published = !$content->is_published;

        if ($content->is_published && !$content->published_at) {
            $content->published_at = now();
        }

        $content->save();

        return response()->json([
            'success' => true,
            'is_published' => $content->is_published,
        ]);
    }

    /**
     * ADMIN API: Store content
     */
    public function apiAdminStore(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'slug' => 'required|string|max:170|unique:content',
            'description' => 'nullable|string',
            'content_url' => 'required|url|max:255',
            'type' => 'required|in:video,blog',
        ]);

        $validated['created_by'] = auth()->id();

        $content = Content::create($validated);

        return response()->json($content, 201);
    }

    /**
     * ADMIN API: Update content
     */
    public function apiAdminUpdate(Request $request, $id)
    {
        $content = Content::findOrFail($id);

        $validated = $request->validate([
            'title' => 'string|max:150',
            'slug' => 'string|max:170|unique:content,slug,' . $id,
            'description' => 'nullable|string',
            'content_url' => 'url|max:255',
            'type' => 'in:video,blog',
        ]);

        $content->update($validated);

        return response()->json($content);
    }

    /**
     * ADMIN API: Delete content
     */
    public function apiAdminDestroy($id)
    {
        $content = Content::findOrFail($id);
        $content->delete();

        return response()->json(['success' => true]);
    }
}
