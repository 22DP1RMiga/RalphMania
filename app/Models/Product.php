<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
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
        'has_sizes',
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

    protected $appends = [
        'discount_percentage',
        'price_ex_vat',
        'vat_amount',
        'name',
        'description',
    ];

    // ─── ATTIECĪBAS ───────────────────────────────────────────────────────

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

    // ─── PALĪGI ─────────────────────────────────────────────────────────────

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

    // ─── AKSESUĀRI ───────────────────────────────────────────────────────────

    /**
     * Atgriež nosaukumu aktīvajā valodā
     */
    public function getNameAttribute(): string
    {
        $locale = App::getLocale();
        return $locale === 'en' ? ($this->name_en ?: $this->name_lv) : $this->name_lv;
    }

    /**
     * Atgriež aprakstu aktīvajā valodā
     */
    public function getDescriptionAttribute(): ?string
    {
        $locale = App::getLocale();
        return $locale === 'en' ? ($this->description_en ?: $this->description_lv) : $this->description_lv;
    }

    public function getDiscountPercentageAttribute(): int
    {
        return $this->discount();
    }

    /**
     * PVN likme - nolasa no iestatījumu tabulas, noklusējuma vērtība ir 21%
     * Kešatmiņa tiek saglabāta statiskā formātā, tāpēc katram
     * pieprasījumam tiek izmantots tikai viens datubāzes vaicājums
     */
    public static function vatRate(): float
    {
        static $rate = null;
        if ($rate === null) {
            $rate = (float) (\App\Models\Setting::get('tax_rate', 21));
        }
        return $rate;
    }

    /**
     * Cena bez PVN (NETO cena).
     * Cena DB ir ar PVN iekļautu (BRUTO) — t.i., 8.99 € ir tas, ko klients maksā.
     * NETO = BRUTO / 1.21
     * Piemērs: 15.00 / 1.21 = 12.3967... → 12.40
     */
    public function getPriceExVatAttribute(): float
    {
        return round((float) $this->price / (1 + self::vatRate() / 100), 2);
    }

    /**
     * Cenā ietvertā PVN summa
     * PVN = BRUTO - NETO
     * Piemērs: 15.00 - 12.40 = 2.60 (vai precīzāk: 15.00 - 12.3967 = 2.6033 → 2.61)
     */
    public function getVatAmountAttribute(): float
    {
        return round((float) $this->price - $this->price_ex_vat, 2);
    }

    /**
     * Tas pats compare_price atribūtam (vecā cena).
     */
    public function getComparePriceExVatAttribute(): ?float
    {
        if (!$this->compare_price) return null;
        return round((float) $this->compare_price / (1 + self::vatRate() / 100), 2);
    }
}
