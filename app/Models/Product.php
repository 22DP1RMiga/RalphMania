<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_lv',
        'name_en',
        'slug',
        'sku',
        'category_id',
        'description_lv',
        'description_en',
        'price',
        'compare_price',
        'image',
        'stock_quantity',
        'low_stock_threshold',
        'is_active',
        'is_featured',
        'has_sizes',   // ← JAUNS: vai produktam ir XS–XXL izmēru izvēlne
    ];

    protected $casts = [
        'price'               => 'decimal:2',
        'compare_price'       => 'decimal:2',
        'is_active'           => 'boolean',
        'is_featured'         => 'boolean',
        'has_sizes'           => 'boolean',
        'stock_quantity'      => 'integer',
        'low_stock_threshold' => 'integer',
    ];

    // ─── RELATIONSHIPS ───────────────────────────────────────────────────────

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // ─── HELPERS ─────────────────────────────────────────────────────────────

    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->low_stock_threshold;
    }

    public function discount(): int
    {
        if (!$this->compare_price) return 0;
        return round((1 - $this->price / $this->compare_price) * 100);
    }

    // ─── ACCESSORS ───────────────────────────────────────────────────────────

    public function getDiscountPercentageAttribute(): int
    {
        return $this->discount();
    }
}
