<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriberOffer extends Model
{
    protected $fillable = [
        'code',
        'title_lv',
        'title_en',
        'description_lv',
        'description_en',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'max_uses',
        'used_count',
        'subscribers_only',
        'is_active',
        'starts_at',
        'expires_at',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'subscribers_only' => 'boolean',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Scope for active offers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('max_uses')->orWhereColumn('used_count', '<', 'max_uses');
            });
    }

    /**
     * Scope for subscriber-only offers.
     */
    public function scopeForSubscribers($query)
    {
        return $query->where('subscribers_only', true);
    }

    /**
     * Get title by locale.
     */
    public function getTitle(string $locale = 'lv'): string
    {
        return $locale === 'en' ? ($this->title_en ?? $this->title_lv) : $this->title_lv;
    }

    /**
     * Get description by locale.
     */
    public function getDescription(string $locale = 'lv'): ?string
    {
        return $locale === 'en' ? ($this->description_en ?? $this->description_lv) : $this->description_lv;
    }

    /**
     * Get formatted discount.
     */
    public function getFormattedDiscount(): string
    {
        if ($this->discount_type === 'percentage') {
            return $this->discount_value . '%';
        }
        return '€' . number_format($this->discount_value, 2);
    }

    /**
     * Check if offer is valid.
     */
    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->starts_at && $this->starts_at > now()) return false;
        if ($this->expires_at && $this->expires_at < now()) return false;
        if ($this->max_uses && $this->used_count >= $this->max_uses) return false;

        return true;
    }

    /**
     * Increment usage count.
     */
    public function incrementUsage(): void
    {
        $this->increment('used_count');
    }
}
