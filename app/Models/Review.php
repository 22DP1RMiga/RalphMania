<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reviewable_type',
        'reviewable_id',
        'rating',
        'review_text_lv',
        'review_text_en',
        'is_approved',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_approved' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Iegūst lietotāju, kurš uzrakstīja atsauksmi
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Iegūst pārskatāmo modeli (saturu vai produktu)
     */
    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Tvērums: tikai apstiprinātas atsauksmes
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Tvērums: tikai gaida atsauksmes
     */
    public function scopePending($query)
    {
        return $query->where('is_approved', false);
    }

    /**
     * Tvērums: pēc reitinga
     */
    public function scopeByRating($query, int $rating)
    {
        return $query->where('rating', $rating);
    }
}
