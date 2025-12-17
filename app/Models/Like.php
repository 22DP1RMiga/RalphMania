<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Like extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'likes';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'likeable_type',
        'likeable_id',
    ];

    /**
     * Get the user that owns the like.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the likeable model (Content, Product, etc.)
     * This is the polymorphic relationship
     */
    public function likeable(): MorphTo
    {
        return $this->morphTo();
    }
}
