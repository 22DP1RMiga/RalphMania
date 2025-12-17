<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'user_id',
        'session_id',
        'product_id',
        'quantity',
        'price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
    ];

    protected $appends = [
        'total',
    ];

    /**
     * Get the cart that owns the item
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user (for guest carts)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get total for this item (quantity * price)
     */
    public function getTotalAttribute(): float
    {
        return (float) ($this->quantity * $this->price);
    }

    /**
     * Boot method to auto-fill price from product
     *
     * CRITICAL: This prevents "Field 'price' doesn't have a default value" error
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cartItem) {
            // If price is not set, get it from product
            if (is_null($cartItem->price) || $cartItem->price == 0) {
                $product = Product::find($cartItem->product_id);
                $cartItem->price = $product ? $product->price : 0;
            }
        });
    }
}
