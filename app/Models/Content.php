<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'published_at' => 'datetime',
        'view_count' => 'integer',
        'like_count' => 'integer',
        'duration' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who created this content
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get comments for this content
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'content_id');
    }

    /**
     * Get localized title
     */
    public function getTitleAttribute(): string
    {
        $locale = app()->getLocale();
        return $locale === 'lv' ? $this->title_lv : ($this->title_en ?? $this->title_lv);
    }

    /**
     * Get localized description
     */
    public function getDescriptionAttribute(): ?string
    {
        $locale = app()->getLocale();
        return $locale === 'lv' ? $this->description_lv : ($this->description_en ?? $this->description_lv);
    }

    /**
     * Get localized content body
     */
    public function getBodyAttribute(): ?string
    {
        $locale = app()->getLocale();
        return $locale === 'lv' ? $this->content_body_lv : ($this->content_body_en ?? $this->content_body_lv);
    }

    /**
     * Get formatted duration (MM:SS)
     */
    public function getFormattedDurationAttribute(): ?string
    {
        if (!$this->duration) {
            return null;
        }

        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * Check if content is a video
     */
    public function isVideo(): bool
    {
        return $this->type === 'video';
    }

    /**
     * Check if content is a blog post
     */
    public function isBlog(): bool
    {
        return $this->type === 'blog';
    }

    /**
     * Get embed URL for video
     */
    public function getEmbedUrlAttribute(): ?string
    {
        if (!$this->video_url || !$this->isVideo()) {
            return null;
        }

        switch ($this->video_platform) {
            case 'YouTube':
                // Extract video ID from various YouTube URL formats
                if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $this->video_url, $matches)) {
                    return "https://www.youtube.com/embed/{$matches[1]}";
                }
                break;

            case 'Vimeo':
                if (preg_match('/vimeo\.com\/(\d+)/', $this->video_url, $matches)) {
                    return "https://player.vimeo.com/video/{$matches[1]}";
                }
                break;

            case 'TikTok':
            case 'Instagram':
            case 'Facebook':
            case 'Twitch':
                // These platforms have different embed methods
                return $this->video_url;
        }

        return $this->video_url;
    }

    /**
     * Scope: Only published content
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope: Only videos
     */
    public function scopeVideos($query)
    {
        return $query->where('type', 'video');
    }

    /**
     * Scope: Only blogs
     */
    public function scopeBlogs($query)
    {
        return $query->where('type', 'blog');
    }

    /**
     * Scope: By category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
