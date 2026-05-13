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
     * Satura tipa konstantes
     */
    const TYPE_VIDEO = 'video';
    const TYPE_BLOG = 'blog';
    const TYPE_NEWS = 'post';
    const TYPE_ANNOUNCEMENT = 'announcement';

    /**
     * Iegūst visus derīgos satura tipus
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
     * Iegūst tipa etiķetes (divvalodu)
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
     * Iegūst lietotāju, kurš izveidoja šo saturu
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Iegūst visas “patīk” atzīmes saturam (polimorfs)
     */
    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    /**
     * Iegūst komentārus par saturu
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'content_id')
            ->where('is_approved', true)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Iegūst atsauksmes par saturu
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'content_id')
            ->where('is_approved', true)
            ->orderBy('created_at', 'desc');
    }

    // =====================================================
    // ATTĒLU URL PALĪGI
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
     * Sīktēlu URL — video: /img/thumbnails/, citi: /storage/blogs/ vai /img/Blogs/
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
     * Piedāvātā attēla URL — blog, post, announcement
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
     * Iegūst redzamā attēla URL (automātiska atlase, pamatojoties uz veidu)
     */
    public function getDisplayImageUrlAttribute(): string
    {
        if ($this->type === self::TYPE_VIDEO) {
            return $this->thumbnail_url;
        }

        return $this->featured_image_url ?? $this->thumbnail_url;
    }

    // =====================================================
    // TIPA PĀRBAUDES PALĪGI
    // =====================================================

    /**
     * Pārbauda, vai saturs ir video
     */
    public function isVideo(): bool
    {
        return $this->type === self::TYPE_VIDEO;
    }

    /**
     * Pārbauda, vai saturs ir emuāra ieraksts
     */
    public function isBlog(): bool
    {
        return $this->type === self::TYPE_BLOG;
    }

    /**
     * Pārbauda, vai saturs ir ziņas (post)
     */
    public function isNews(): bool
    {
        return $this->type === self::TYPE_NEWS;
    }

    /**
     * Pārbauda, vai saturs ir paziņojums
     */
    public function isAnnouncement(): bool
    {
        return $this->type === self::TYPE_ANNOUNCEMENT;
    }

    /**
     * Pārbauda, vai saturam ir pamatteksts (emuāri, ziņas, paziņojumi)
     */
    public function hasBodyContent(): bool
    {
        return in_array($this->type, [self::TYPE_BLOG, self::TYPE_NEWS, self::TYPE_ANNOUNCEMENT]);
    }

    // =====================================================
    // VAICĀJUMA TVĒRUMI
    // =====================================================

    /**
     * Tvērums: tikai publicēts saturs
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where('published_at', '<=', now());
    }

    /**
     * Tvērums: tikai piedāvātais saturs
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Tvērums: filtrē pēc tipa
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Tvērums: tikai video
     */
    public function scopeVideos($query)
    {
        return $query->where('type', self::TYPE_VIDEO);
    }

    /**
     * Tvērums: tikai emuāri
     */
    public function scopeBlogs($query)
    {
        return $query->where('type', self::TYPE_BLOG);
    }

    /**
     * Tvērums: tikai ziņas
     */
    public function scopeNews($query)
    {
        return $query->where('type', self::TYPE_NEWS);
    }

    /**
     * Tvērums: tikai paziņojumi
     */
    public function scopeAnnouncements($query)
    {
        return $query->where('type', self::TYPE_ANNOUNCEMENT);
    }

    /**
     * Tvērums: filtrē pēc kategorijas
     */
    public function scopeInCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Tvērums: jaunākais pirmais
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('published_at', 'desc');
    }

    /**
     * Tvērums: populārākais (pēc skatījumiem)
     */
    public function scopePopular($query)
    {
        return $query->orderBy('view_count', 'desc');
    }

    // =====================================================
    // MARŠRUTA MODEĻA SAISTĪŠANA
    // =====================================================

    /**
     * Iegūst modeļa maršruta atslēgu
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
