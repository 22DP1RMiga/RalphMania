<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;

class AdminContentController extends Controller
{
    /**
     * Display a listing of content for admin.
     */
    public function index(Request $request)
    {
        $query = Content::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title_lv', 'like', "%{$search}%")
                    ->orWhere('title_en', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            if ($request->status === 'published') {
                $query->where('is_published', true);
            } else {
                $query->where('is_published', false);
            }
        }

        $content = $query->orderBy('created_at', 'desc')
            ->paginate(12)
            ->through(fn ($item) => [
                'id' => $item->id,
                'title_lv' => $item->title_lv,
                'title_en' => $item->title_en,
                'slug' => $item->slug,
                'type' => $item->type,
                'category' => $item->category,
                'thumbnail' => $item->thumbnail,
                'featured_image' => $item->featured_image,
                'view_count' => $item->view_count,
                'like_count' => $item->like_count,
                'duration' => $item->duration,
                'is_published' => $item->is_published,
                'is_featured' => $item->is_featured,
                'published_at' => $item->published_at,
                'created_at' => $item->created_at,
            ]);

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
            'type' => 'required|in:video,blog,news,announcement',
            'title_lv' => 'required|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'slug' => 'required|string|max:255|unique:content,slug',
            'category' => 'nullable|string|max:100',
            'description_lv' => 'nullable|string',
            'description_en' => 'nullable|string',
            'content_body_lv' => 'nullable|string',
            'content_body_en' => 'nullable|string',
            'video_url' => 'nullable|url',
            'video_platform' => 'nullable|in:youtube,vimeo,other',
            'duration' => 'nullable|integer|min:0',
            'thumbnail' => 'nullable|image|max:5120',
            'featured_image' => 'nullable|image|max:5120',
            'blog_images' => 'nullable|array',
            'blog_images.*' => 'image|max:5120',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        // Handle thumbnail upload (videos)
        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('img/thumbnails'), $filename);
            $validated['thumbnail'] = $filename;
        }

        // Handle featured image upload (blogs, news, announcements)
        if ($request->hasFile('featured_image')) {
            $file = $request->file('featured_image');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('img/Blogs'), $filename);
            $validated['featured_image'] = $filename;
        }

        // Handle multiple blog images
        if ($request->hasFile('blog_images')) {
            $blogImages = [];
            foreach ($request->file('blog_images') as $file) {
                $filename = time() . '_' . uniqid() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('img/Blogs'), $filename);
                $blogImages[] = $filename;
            }
            $validated['blog_images'] = $blogImages;
        }

        // Set published_at if publishing and not set
        if ($validated['is_published'] && empty($validated['published_at'])) {
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
            'type' => 'required|in:video,blog,news,announcement',
            'title_lv' => 'required|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'slug' => 'required|string|max:255|unique:content,slug,' . $id,
            'category' => 'nullable|string|max:100',
            'description_lv' => 'nullable|string',
            'description_en' => 'nullable|string',
            'content_body_lv' => 'nullable|string',
            'content_body_en' => 'nullable|string',
            'video_url' => 'nullable|url',
            'video_platform' => 'nullable|in:youtube,vimeo,other',
            'duration' => 'nullable|integer|min:0',
            'thumbnail' => 'nullable|image|max:5120',
            'featured_image' => 'nullable|image|max:5120',
            'blog_images' => 'nullable|array',
            'blog_images.*' => 'image|max:5120',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'published_at' => 'nullable|date',
            '_remove_thumbnail' => 'boolean',
            '_remove_featured_image' => 'boolean',
            '_remove_blog_images' => 'nullable|array',
        ]);

        // Handle thumbnail removal
        if ($request->boolean('_remove_thumbnail') && $content->thumbnail) {
            $oldPath = public_path('img/thumbnails/' . $content->thumbnail);
            if (File::exists($oldPath)) {
                File::delete($oldPath);
            }
            $validated['thumbnail'] = null;
        }

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($content->thumbnail) {
                $oldPath = public_path('img/thumbnails/' . $content->thumbnail);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }

            $file = $request->file('thumbnail');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('img/thumbnails'), $filename);
            $validated['thumbnail'] = $filename;
        }

        // Handle featured image removal
        if ($request->boolean('_remove_featured_image') && $content->featured_image) {
            $oldPath = public_path('img/Blogs/' . $content->featured_image);
            if (File::exists($oldPath)) {
                File::delete($oldPath);
            }
            $validated['featured_image'] = null;
        }

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            // Delete old featured image
            if ($content->featured_image) {
                $oldPath = public_path('img/Blogs/' . $content->featured_image);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }

            $file = $request->file('featured_image');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('img/Blogs'), $filename);
            $validated['featured_image'] = $filename;
        }

        // Handle blog images removal
        if ($request->has('_remove_blog_images') && is_array($request->_remove_blog_images)) {
            $currentImages = $content->blog_images ?? [];
            $removeIndices = $request->_remove_blog_images;

            foreach ($removeIndices as $index) {
                if (isset($currentImages[$index])) {
                    $oldPath = public_path('img/Blogs/' . $currentImages[$index]);
                    if (File::exists($oldPath)) {
                        File::delete($oldPath);
                    }
                    unset($currentImages[$index]);
                }
            }

            $validated['blog_images'] = array_values($currentImages);
        }

        // Handle new blog images upload
        if ($request->hasFile('blog_images')) {
            $existingImages = $validated['blog_images'] ?? $content->blog_images ?? [];

            foreach ($request->file('blog_images') as $file) {
                $filename = time() . '_' . uniqid() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('img/Blogs'), $filename);
                $existingImages[] = $filename;
            }

            $validated['blog_images'] = $existingImages;
        }

        // Set published_at if publishing for first time
        if ($validated['is_published'] && !$content->published_at) {
            $validated['published_at'] = now();
        }

        // Remove internal fields
        unset($validated['_remove_thumbnail']);
        unset($validated['_remove_featured_image']);
        unset($validated['_remove_blog_images']);

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

        // Delete thumbnail
        if ($content->thumbnail) {
            $path = public_path('img/thumbnails/' . $content->thumbnail);
            if (File::exists($path)) {
                File::delete($path);
            }
        }

        // Delete featured image
        if ($content->featured_image) {
            $path = public_path('img/Blogs/' . $content->featured_image);
            if (File::exists($path)) {
                File::delete($path);
            }
        }

        // Delete blog images
        if ($content->blog_images && is_array($content->blog_images)) {
            foreach ($content->blog_images as $image) {
                $path = public_path('img/Blogs/' . $image);
                if (File::exists($path)) {
                    File::delete($path);
                }
            }
        }

        $content->delete();

        return back()->with('success', 'Saturs veiksmīgi dzēsts!');
    }
}
