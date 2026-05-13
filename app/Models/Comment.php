<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content_id',
        'comment_text',
        'parent_id',
        'is_approved',
        'mood_score',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'mood_score'  => 'integer',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    /**
     * Iegūst lietotāju, kurš uzrakstīja komentāru
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Iegūst saturu, kuram tika pievienots komentārs
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    /**
     * Iegūst vecāku komentāru (pavedieniem jeb "threads")
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Saņem bērnu komentārus (atbildes)
     */
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    /**
     * Tvērums: Tikai apstiprinātie komentāri
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Tvērums: Tikai "root" komentāri (nevis atbildes)
     */
    public function scopeRootOnly($query)
    {
        return $query->whereNull('parent_id');
    }
}
