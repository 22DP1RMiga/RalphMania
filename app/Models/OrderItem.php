<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * No timestamps for order items (only created_at in DB)
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * ✅ FIXED: Matches database schema exactly
     * - Has product_name (required)
     * - No subtotal (DB has auto-calculated 'total' instead)
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'quantity',
        'price',
        // Note: 'total' is GENERATED column in DB (quantity * price)
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
    ];

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'total',
    ];

    /**
     * Get the order this item belongs to
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product (original product reference)
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate total (quantity × price)
     *
     * This matches the DB's GENERATED column
     */
    public function getTotalAttribute(): float
    {
        // If 'total' exists in attributes (from DB), use it
        if (isset($this->attributes['total'])) {
            return (float) $this->attributes['total'];
        }

        // Otherwise calculate it
        return (float) ($this->quantity * $this->price);
    }
}
