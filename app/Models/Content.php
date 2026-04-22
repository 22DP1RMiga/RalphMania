<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Content extends Model
{
    use HasFactory;

    protected $table = 'content';

    protected $fillable = [
        'title_lv',
        'title_en',
        'slug',
        'type',
        'description_lv',
        'description_en',
        'content_body_lv',
        'content_body_en',
        'video_url',
        'video_platform',
        'thumbnail',
        'featured_image',
        'blog_images',
        'duration',
        'category',
        'view_count',
        'like_count',
        'is_published',
        'is_featured',
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
        'view_count' => 'integer',
        'like_count' => 'integer',
        'duration' => 'integer',
        'blog_images' => 'array',
    ];

    /**
     * Content type constants
     */
    const TYPE_VIDEO = 'video';
    const TYPE_BLOG = 'blog';
    const TYPE_NEWS = 'post';
    const TYPE_ANNOUNCEMENT = 'announcement';

    /**
     * Get all valid content types
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_VIDEO,
            self::TYPE_BLOG,
            self::TYPE_NEWS,
            self::TYPE_ANNOUNCEMENT,
        ];
    }

    /**
     * Get type labels (bilingual)
     */
    public static function getTypeLabels(): array
    {
        return [
            self::TYPE_VIDEO => ['lv' => 'Video', 'en' => 'Video'],
            self::TYPE_BLOG => ['lv' => 'Blogs', 'en' => 'Blog'],
            self::TYPE_NEWS => ['lv' => 'Ziņas', 'en' => 'Posts'],
            self::TYPE_ANNOUNCEMENT => ['lv' => 'Paziņojums', 'en' => 'Announcement'],
        ];
    }

    /**
     * Get the user that created this content.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all likes for the content (polymorphic)
     */
    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    /**
     * Get the comments for the content.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'content_id')
            ->where('is_approved', true)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the reviews for the content.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'content_id')
            ->where('is_approved', true)
            ->orderBy('created_at', 'desc');
    }

    // =====================================================
    // IMAGE URL HELPERS
    // =====================================================

    /**
     * Universāls attēla URL aprēķins.
     * Apstrādā: http/https URL, /absolūts ceļš, storage/... relatīvs, tikai faila nosaukums.
     */
    private function resolveImageUrl(?string $path, string $legacyDir = '/img/thumbnails'): ?string
    {
        if (!$path) return null;
        if (str_starts_with($path, 'http')) return $path;
        if (str_starts_with($path, '/'))   return $path;
        if (str_starts_with($path, 'storage/')) return '/' . $path;
        return $legacyDir . '/' . $path;
    }

    /**
     * Thumbnail URL — video: /img/thumbnails/, citi: /storage/blogs/ vai /img/Blogs/
     */
    public function getThumbnailUrlAttribute(): string
    {
        if (!$this->thumbnail) {
            return '/img/no-content-placeholder.png';
        }
        $dir = match($this->type) {
            'video'        => '/img/thumbnails',
            'blog'         => '/img/Blogs',
            'post'         => '/img/Posts',
            'announcement' => '/img/Announcements',
            default        => '/img/thumbnails',
        };
        return $this->resolveImageUrl($this->thumbnail, $dir) ?? '/img/no-content-placeholder.png';
    }

    /**
     * Featured image URL — blog, post, announcement
     */
    public function getFeaturedImageUrlAttribute(): string
    {
        if (!$this->featured_image) {
            return '/img/no-content-placeholder.png';
        }
        $dir = match($this->type) {
            'blog'         => '/img/Blogs',
            'post'         => '/img/Posts',
            'announcement' => '/img/Announcements',
            default        => '/img/Blogs',
        };
        return $this->resolveImageUrl($this->featured_image, $dir) ?? '/img/no-content-placeholder.png';
    }

    /**
     * Blog/post attēlu URL masīvs
     */
    public function getBlogImageUrlsAttribute(): array
    {
        if (!$this->blog_images || !is_array($this->blog_images)) {
            return [];
        }
        return array_filter(array_map(function ($image) {
            return $this->resolveImageUrl($image, '/img/Blogs');
        }, $this->blog_images));
    }

    /**
     * Get the display image URL (auto-selects based on type)
     */
    public function getDisplayImageUrlAttribute(): string
    {
        if ($this->type === self::TYPE_VIDEO) {
            return $this->thumbnail_url;
        }

        return $this->featured_image_url ?? $this->thumbnail_url;
    }

    // =====================================================
    // TYPE CHECKING HELPERS
    // =====================================================

    /**
     * Check if content is a video
     */
    public function isVideo(): bool
    {
        return $this->type === self::TYPE_VIDEO;
    }

    /**
     * Check if content is a blog post
     */
    public function isBlog(): bool
    {
        return $this->type === self::TYPE_BLOG;
    }

    /**
     * Check if content is news
     */
    public function isNews(): bool
    {
        return $this->type === self::TYPE_NEWS;
    }

    /**
     * Check if content is an announcement
     */
    public function isAnnouncement(): bool
    {
        return $this->type === self::TYPE_ANNOUNCEMENT;
    }

    /**
     * Check if content has body text (blogs, news, announcements)
     */
    public function hasBodyContent(): bool
    {
        return in_array($this->type, [self::TYPE_BLOG, self::TYPE_NEWS, self::TYPE_ANNOUNCEMENT]);
    }

    // =====================================================
    // QUERY SCOPES
    // =====================================================

    /**
     * Scope: only published content
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where('published_at', '<=', now());
    }

    /**
     * Scope: only featured content
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope: filter by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: only videos
     */
    public function scopeVideos($query)
    {
        return $query->where('type', self::TYPE_VIDEO);
    }

    /**
     * Scope: only blogs
     */
    public function scopeBlogs($query)
    {
        return $query->where('type', self::TYPE_BLOG);
    }

    /**
     * Scope: only news
     */
    public function scopeNews($query)
    {
        return $query->where('type', self::TYPE_NEWS);
    }

    /**
     * Scope: only announcements
     */
    public function scopeAnnouncements($query)
    {
        return $query->where('type', self::TYPE_ANNOUNCEMENT);
    }

    /**
     * Scope: filter by category
     */
    public function scopeInCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: latest first
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('published_at', 'desc');
    }

    /**
     * Scope: most popular (by views)
     */
    public function scopePopular($query)
    {
        return $query->orderBy('view_count', 'desc');
    }

    // =====================================================
    // ROUTE MODEL BINDING
    // =====================================================

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
