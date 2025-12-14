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

    /**
     * ✅ NEW: Get full URL for featured image
     */
    public function getFeaturedImageUrlAttribute(): ?string
    {
        if (!$this->featured_image) {
            return null;
        }

        // If it's already a full URL
        if (str_starts_with($this->featured_image, 'http')) {
            return $this->featured_image;
        }

        // Build storage URL
        return '/storage/' . $this->featured_image;
    }

    /**
     * ✅ NEW: Get full URLs for all blog images
     */
    public function getBlogImageUrlsAttribute(): array
    {
        if (!$this->blog_images || !is_array($this->blog_images)) {
            return [];
        }

        return array_map(function ($image) {
            if (str_starts_with($image, 'http')) {
                return $image;
            }
            return '/storage/' . $image;
        }, $this->blog_images);
    }

    /**
     * ✅ NEW: Check if content is a blog post
     */
    public function isBlog(): bool
    {
        return $this->type === 'blog';
    }

    /**
     * ✅ NEW: Check if content is a video
     */
    public function isVideo(): bool
    {
        return $this->type === 'video';
    }

    /**
     * Scope a query to only include published content.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where('published_at', '<=', now());
    }

    /**
     * Scope a query to only include featured content.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to filter by type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * ✅ NEW: Scope for blogs only
     */
    public function scopeBlogs($query)
    {
        return $query->where('type', 'blog');
    }

    /**
     * ✅ NEW: Scope for videos only
     */
    public function scopeVideos($query)
    {
        return $query->where('type', 'video');
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeInCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
