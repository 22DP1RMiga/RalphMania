<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'view_count' => 'integer',
        'like_count' => 'integer',
        'duration' => 'integer',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'content_id');
    }

    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->whereNotNull('published_at');
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeVideo($query)
    {
        return $query->where('type', 'video');
    }

    public function scopeBlog($query)
    {
        return $query->where('type', 'blog');
    }
}
