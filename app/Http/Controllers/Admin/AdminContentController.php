<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
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
            ->through(function ($item) {
                // Vidējais noskaņojums no comment_moods caur komentāriem
                $moodData = \DB::table('comment_moods')
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
                    'avg_mood_score' => $moodData->avg_score !== null ? round($moodData->avg_score) : null,
                    'mood_count' => (int)($moodData->mood_count ?? 0),
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
            'type'             => 'required|in:video,blog,post,announcement',
            'title_lv'         => 'required|string|max:255',
            'title_en'         => 'nullable|string|max:255',
            'slug'             => 'required|string|max:255|unique:content,slug',
            'category'         => 'nullable|string|max:100',
            'description_lv'   => 'nullable|string',
            'description_en'   => 'nullable|string',
            'content_body_lv'  => 'nullable|string',
            'content_body_en'  => 'nullable|string',
            'video_url'        => 'nullable|url',
            'video_platform'   => 'nullable|in:YouTube,TikTok,Instagram,Facebook,X,Vimeo,Other',
            'duration'         => 'nullable|integer|min:0',
            'thumbnail'        => 'nullable|image|max:5120',
            'featured_image'   => 'nullable|image|max:5120',
            'blog_images'      => 'nullable|array',
            'blog_images.*'    => 'image|max:5120',
            'is_published'     => 'boolean',
            'is_featured'      => 'boolean',
            'published_at'     => 'nullable|date',
        ]);

        $type = $validated['type'];

        // ── Attēlu saglabāšana zem public/img/ ─────────────────────

        // Sīktēls (tikai video)
        if ($request->hasFile('thumbnail')) {
            $file     = $request->file('thumbnail');
            $filename = time() . '_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('img/thumbnails'), $filename);
            $validated['thumbnail'] = '/img/thumbnails/' . $filename;
        }

        // Galvenais attēls (blog, post, announcement)
        if ($request->hasFile('featured_image')) {
            $file     = $request->file('featured_image');
            $filename = time() . '_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $dir      = $this->getContentImgDir($type);
            $file->move(public_path($dir), $filename);
            $validated['featured_image'] = '/' . $dir . '/' . $filename;
        }

        // Ne-video tips: featured_image darbojas arī kā thumbnail (sīktēls katalogā)
        if ($type !== 'video' && !empty($validated['featured_image'])) {
            $validated['thumbnail'] = $validated['featured_image'];
        }

        // Vairāki attēli (blog, post)
        if ($request->hasFile('blog_images')) {
            $blogImages = [];
            $dir = $this->getContentImgDir($type);
            foreach ($request->file('blog_images') as $file) {
                $filename = time() . '_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path($dir), $filename);
                $blogImages[] = '/' . $dir . '/' . $filename;
            }
            $validated['blog_images'] = $blogImages;
        }

        // Publicēšanas laiks
        if (!empty($validated['is_published']) && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

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
            'type'                    => 'required|in:video,blog,post,announcement',
            'title_lv'                => 'required|string|max:255',
            'title_en'                => 'nullable|string|max:255',
            'slug'                    => 'required|string|max:255|unique:content,slug,' . $id,
            'category'                => 'nullable|string|max:100',
            'description_lv'          => 'nullable|string',
            'description_en'          => 'nullable|string',
            'content_body_lv'         => 'nullable|string',
            'content_body_en'         => 'nullable|string',
            'video_url'               => 'nullable|url',
            'video_platform'          => 'nullable|in:YouTube,TikTok,Instagram,Facebook,X,Vimeo,Other',
            'duration'                => 'nullable|integer|min:0',
            'thumbnail'               => 'nullable|image|max:5120',
            'featured_image'          => 'nullable|image|max:5120',
            'blog_images'             => 'nullable|array',
            'blog_images.*'           => 'image|max:5120',
            'is_published'            => 'boolean',
            'is_featured'             => 'boolean',
            'published_at'            => 'nullable|date',
            '_remove_thumbnail'       => 'boolean',
            '_remove_featured_image'  => 'boolean',
            '_remove_blog_images'     => 'nullable|array',
        ]);

        $type = $validated['type'];

        // ── Sīktēla noņemšana (video) ──────────────────────────────
        if ($request->boolean('_remove_thumbnail') && $content->thumbnail) {
            $this->deleteStorageFile($content->thumbnail);
            $validated['thumbnail'] = null;
        }

        // ── Sīktēla augšupielāde (video) ───────────────────────────
        if ($request->hasFile('thumbnail')) {
            $this->deleteStorageFile($content->thumbnail);
            $file     = $request->file('thumbnail');
            $filename = time() . '_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('img/thumbnails'), $filename);
            $validated['thumbnail'] = '/img/thumbnails/' . $filename;
        }

        // ── Galvenā attēla noņemšana (ne-video) ────────────────────
        if ($request->boolean('_remove_featured_image') && $content->featured_image) {
            $this->deleteStorageFile($content->featured_image);
            $validated['featured_image'] = null;
            // Ja tas bija arī sīktēls — notīra arī thumbnail
            if ($content->thumbnail === $content->featured_image) {
                $validated['thumbnail'] = null;
            }
        }

        // ── Galvenā attēla augšupielāde (ne-video) ──────────────────
        if ($request->hasFile('featured_image')) {
            $this->deleteStorageFile($content->featured_image);
            $file     = $request->file('featured_image');
            $filename = time() . '_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $dir = $this->getContentImgDir($type);
            $file->move(public_path($dir), $filename);
            $validated['featured_image'] = '/' . $dir . '/' . $filename;
        }

        // Ne-video: featured_image = thumbnail
        if ($type !== 'video' && !empty($validated['featured_image'])) {
            $validated['thumbnail'] = $validated['featured_image'];
        }

        // ── Galerijas attēlu dzēšana ─────────────────────────────────
        if ($request->has('_remove_blog_images') && is_array($request->_remove_blog_images)) {
            $currentImages  = $content->blog_images ?? [];
            $removeIndices  = $request->_remove_blog_images;

            foreach ($removeIndices as $index) {
                if (isset($currentImages[$index])) {
                    $this->deleteStorageFile($currentImages[$index]);
                    unset($currentImages[$index]);
                }
            }
            $validated['blog_images'] = array_values($currentImages);
        }

        // ── Galerijas attēlu augšupielāde ───────────────────────────
        if ($request->hasFile('blog_images')) {
            $existingImages = $validated['blog_images'] ?? $content->blog_images ?? [];

            foreach ($request->file('blog_images') as $file) {
                $filename = time() . '_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
                $dir = $this->getContentImgDir($type);
                $file->move(public_path($dir), $filename);
                $existingImages[] = '/' . $dir . '/' . $filename;
            }
            $validated['blog_images'] = $existingImages;
        }

        // Publicēšanas laiks
        if (!empty($validated['is_published']) && !$content->published_at) {
            $validated['published_at'] = now();
        }

        // Noņem iekšējos laukus
        unset($validated['_remove_thumbnail'], $validated['_remove_featured_image'], $validated['_remove_blog_images']);

        $content->update($validated);

        return redirect()->route('admin.content.index')
            ->with('success', 'Saturs veiksmīgi atjaunināts!');
    }

    /**
     * Dzēš failu no storage (pieņem gan 'storage/...' gan '/storage/...' ceļus).
     * Veco /img/ ceļus ignorē — tos nedrīkst dzēst automātiski.
     */
    /**
     * Nosaka attēlu mapi pēc satura tipa.
     * video → img/thumbnails, blog → img/Blogs, post → img/Posts, announcement → img/Announcements
     */
    private function getContentImgDir(string $type): string
    {
        return match($type) {
            'blog'         => 'img/Blogs',
            'post'         => 'img/Posts',
            'announcement' => 'img/Announcements',
            default        => 'img/thumbnails',
        };
    }

    private function deleteStorageFile(?string $path): void
    {
        if (!$path) return;
        $relative = ltrim($path, '/');
        // /img/ ceļi — dzēš no public/
        if (str_starts_with($relative, 'img/')) {
            $full = public_path($relative);
            if (file_exists($full)) @unlink($full);
            return;
        }
        // storage/ ceļi
        if (str_starts_with($relative, 'storage/')) {
            $storagePath = str_replace('storage/', 'public/', $relative);
            if (Storage::exists($storagePath)) {
                Storage::delete($storagePath);
            }
        }
    }

    /**
     * Ātrs statusa maiņas endpoint (no Index lapas) — pieņem tikai pamata laukus.
     * Šis tiek izsaukts kad nospiež publish/draft toggle Admin/Content/Index.vue.
     */
    public function quickUpdate(Request $request, $id)
    {
        $content = Content::findOrFail($id);

        $validated = $request->validate([
            'type'         => 'required|in:video,blog,post,announcement',
            'title_lv'     => 'required|string|max:255',
            'title_en'     => 'nullable|string|max:255',
            'slug'         => 'required|string|max:255|unique:content,slug,' . $id,
            'is_published' => 'boolean',
            'is_featured'  => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        // Ja publicē pirmo reizi, iestata published_at
        if (!empty($validated['is_published']) && !$content->published_at) {
            $validated['published_at'] = now();
        }

        $content->update($validated);

        return redirect()->back()->with('success', 'Statuss mainīts!');
    }

    /**
     * Remove the specified content.
     */
    public function destroy($id)
    {
        $content = Content::findOrFail($id);

        $this->deleteStorageFile($content->thumbnail);
        // featured_image var sakrist ar thumbnail — dzēš tikai ja atšķiras
        if ($content->featured_image && $content->featured_image !== $content->thumbnail) {
            $this->deleteStorageFile($content->featured_image);
        }
        if ($content->blog_images && is_array($content->blog_images)) {
            foreach ($content->blog_images as $image) {
                $this->deleteStorageFile($image);
            }
        }

        $content->delete();

        return back()->with('success', 'Saturs veiksmīgi dzēsts!');
    }
}
