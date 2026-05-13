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
        'size',
        'price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price'    => 'decimal:2',
    ];

    protected $appends = ['total'];

    // ─── ATTIECĪBAS ───────────────────────────────────────────────────────

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── AKSESUĀRI ───────────────────────────────────────────────────────────

    public function getTotalAttribute(): float
    {
        return (float) ($this->quantity * $this->price);
    }

    // ─── BOOT ────────────────────────────────────────────────────────────────

    /**
     * Automātiski aizpilda cenu no produkta, ja nav norādīta.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cartItem) {
            if (is_null($cartItem->price) || $cartItem->price == 0) {
                $product = Product::find($cartItem->product_id);
                $cartItem->price = $product ? $product->price : 0;
            }
        });
    }
}
