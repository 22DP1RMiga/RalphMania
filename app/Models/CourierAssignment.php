<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourierAssignment extends Model
{
    protected $fillable = [
        'courier_id',
        'order_id',
        'assigned_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'assigned_at'  => 'datetime',
        'completed_at' => 'datetime',
    ];

    // ─── RELATIONSHIPS ────────────────────────────────────────────────────────

    public function courier(): BelongsTo
    {
        return $this->belongsTo(Courier::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // ─── ACCESSORS ────────────────────────────────────────────────────────────

    public function getIsCompletedAttribute(): bool
    {
        return $this->completed_at !== null;
    }

    public function getDaysActiveAttribute(): int
    {
        $start = $this->assigned_at ?? $this->created_at;
        return (int) now()->diffInDays($start);
    }
}
