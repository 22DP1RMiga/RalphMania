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
     * Ar modeli saistītā tabula
     */
    protected $table = 'likes';

    /**
     * Atribūti, kurus var piešķirt masveidā
     */
    protected $fillable = [
        'user_id',
        'likeable_type',
        'likeable_id',
    ];

    /**
     * Iegūst lietotāju, kuram pieder atzīme “patīk”
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Iegūst tīkamu modeli (saturs, produkts utt.)
     * Šīs ir polimorfās attiecības
     */
    public function likeable(): MorphTo
    {
        return $this->morphTo();
    }
}
