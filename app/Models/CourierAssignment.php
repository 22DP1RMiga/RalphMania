<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourierAssignment extends Model
{
    /**
     * courier_assignments tabula neietver created_at/updated_at kolonnas
     * Bez šī iestatījuma Eloquent mēģina ierakstīt šos laukus un 500 kļūda rodas
     */
    public $timestamps = false;

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

    // ─── ATTIECĪBAS ────────────────────────────────────────────────────────

    public function courier(): BelongsTo
    {
        return $this->belongsTo(Courier::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // ─── AKSESUĀRI ────────────────────────────────────────────────────────────

    public function getIsCompletedAttribute(): bool
    {
        return $this->completed_at !== null;
    }

    public function getDaysActiveAttribute(): int
    {
        $start = $this->assigned_at ?? now();
        return (int) now()->diffInDays($start);
    }
}
