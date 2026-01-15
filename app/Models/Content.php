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
    const TYPE_NEWS = 'news';
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
            self::TYPE_NEWS => ['lv' => 'Ziņas', 'en' => 'News'],
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
     * Get thumbnail URL based on content type
     * Videos: /img/thumbnails/{filename}
     * Others: /img/Blogs/{filename} or /img/thumbnails/{filename}
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail) {
            return '/img/thumbnails/no-content-placeholder.png';
        }

        // Already a full URL
        if (str_starts_with($this->thumbnail, 'http') || str_starts_with($this->thumbnail, '/')) {
            return $this->thumbnail;
        }

        // Video thumbnails in /img/thumbnails/
        return '/img/thumbnails/' . $this->thumbnail;
    }

    /**
     * Get featured image URL (for blogs, news, announcements)
     * Located in /img/Blogs/
     */
    public function getFeaturedImageUrlAttribute(): ?string
    {
        if (!$this->featured_image) {
            // Return placeholder for non-video types
            if ($this->type !== self::TYPE_VIDEO) {
                return '/img/Blogs/no-content-placeholder.png';
            }
            return null;
        }

        // Already a full URL
        if (str_starts_with($this->featured_image, 'http') || str_starts_with($this->featured_image, '/')) {
            return $this->featured_image;
        }

        // Blog/news images in /img/Blogs/
        return '/img/Blogs/' . $this->featured_image;
    }

    /**
     * Get full URLs for all blog images
     */
    public function getBlogImageUrlsAttribute(): array
    {
        if (!$this->blog_images || !is_array($this->blog_images)) {
            return [];
        }

        return array_map(function ($image) {
            if (str_starts_with($image, 'http') || str_starts_with($image, '/')) {
                return $image;
            }
            return '/img/Blogs/' . $image;
        }, $this->blog_images);
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
