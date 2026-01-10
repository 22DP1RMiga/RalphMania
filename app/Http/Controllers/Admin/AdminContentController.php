<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AdminContentController extends Controller
{
    /**
     * Display a listing of content for admin.
     */
    public function index(Request $request)
    {
        $query = Content::query();

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title_lv', 'LIKE', "%{$search}%")
                    ->orWhere('title_en', 'LIKE', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'published') {
                $query->where('is_published', true);
            } elseif ($request->status === 'draft') {
                $query->where('is_published', false);
            }
        }

        // Sort
        $query->orderBy('created_at', 'desc');

        // Paginate
        $content = $query->paginate(20)->through(function ($item) {
            return [
                'id' => $item->id,
                'title_lv' => $item->title_lv,
                'title_en' => $item->title_en,
                'slug' => $item->slug,
                'type' => $item->type,
                'thumbnail' => $item->thumbnail,
                'is_published' => $item->is_published,
                'is_featured' => $item->is_featured,
                'view_count' => $item->view_count,
                'like_count' => $item->like_count,
                'published_at' => $item->published_at,
                'created_at' => $item->created_at,
            ];
        });

        return Inertia::render('Admin/Content/Index', [
            'content' => $content,
            'filters' => $request->only(['search', 'type', 'status']),
        ]);
    }

    /**
     * Show the form for creating new content.
     */
    public function create()
    {
        return Inertia::render('Admin/Content/Create');
    }

    /**
     * Store newly created content.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title_lv' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'type' => 'required|in:video,blog,news,announcement',
            'description_lv' => 'nullable|string',
            'description_en' => 'nullable|string',
            'body_lv' => 'nullable|string',
            'body_en' => 'nullable|string',
            'thumbnail' => 'nullable|image|max:2048',
            'video_url' => 'nullable|url',
            'video_platform' => 'nullable|in:youtube,vimeo,tiktok',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        // Generate slug
        $validated['slug'] = Str::slug($validated['title_en']);

        // Ensure unique slug
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (Content::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('content', 'public');
        }

        // Set published_at if publishing
        if ($validated['is_published'] && !isset($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        // Set created_by
        $validated['created_by'] = auth()->id();

        Content::create($validated);

        return redirect()->route('admin.content.index')
            ->with('success', 'Saturs veiksmīgi izveidots!');
    }

    /**
     * Show the form for editing content.
     */
    public function edit($id)
    {
        $content = Content::findOrFail($id);

        return Inertia::render('Admin/Content/Edit', [
            'content' => $content,
        ]);
    }

    /**
     * Update the specified content.
     */
    public function update(Request $request, $id)
    {
        $content = Content::findOrFail($id);

        $validated = $request->validate([
            'title_lv' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'type' => 'required|in:video,blog,news,announcement',
            'description_lv' => 'nullable|string',
            'description_en' => 'nullable|string',
            'body_lv' => 'nullable|string',
            'body_en' => 'nullable|string',
            'thumbnail' => 'nullable|image|max:2048',
            'video_url' => 'nullable|url',
            'video_platform' => 'nullable|in:youtube,vimeo,tiktok',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($content->thumbnail && Storage::disk('public')->exists($content->thumbnail)) {
                Storage::disk('public')->delete($content->thumbnail);
            }
            $validated['thumbnail'] = $request->file('thumbnail')->store('content', 'public');
        }

        // Set published_at if publishing for first time
        if ($validated['is_published'] && !$content->published_at) {
            $validated['published_at'] = now();
        }

        $content->update($validated);

        return redirect()->route('admin.content.index')
            ->with('success', 'Saturs veiksmīgi atjaunināts!');
    }

    /**
     * Remove the specified content.
     */
    public function destroy($id)
    {
        $content = Content::findOrFail($id);

        // Delete thumbnail if exists
        if ($content->thumbnail && Storage::disk('public')->exists($content->thumbnail)) {
            Storage::disk('public')->delete($content->thumbnail);
        }

        $content->delete();

        return back()->with('success', 'Saturs veiksmīgi dzēsts!');
    }
}
