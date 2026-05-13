<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Courier extends Model
{
    protected $fillable = [
        'user_id',
        'full_name',
        'vehicle_type',
        'delivery_area',
        'phone',
        'is_active',
        'hired_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'hired_at'  => 'date',
    ];

    // ─── ATTIECĪBAS ────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(CourierAssignment::class);
    }

    public function activeAssignments(): HasMany
    {
        return $this->hasMany(CourierAssignment::class)
            ->whereNull('completed_at');
    }

    public function completedAssignments(): HasMany
    {
        return $this->hasMany(CourierAssignment::class)
            ->whereNotNull('completed_at');
    }

    // ─── TVĒRUMI ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ─── AKSESUĀRI ────────────────────────────────────────────────────────────

    /**
     * Šim kurjeram piešķirto aktīvo (nepiegādāto) pasūtījumu skaits
     */
    public function getActiveOrdersCountAttribute(): int
    {
        return $this->activeAssignments()->count();
    }

    /**
     * Kopējais pabeigto piegāžu skaits
     */
    public function getCompletedOrdersCountAttribute(): int
    {
        return $this->completedAssignments()->count();
    }
}
