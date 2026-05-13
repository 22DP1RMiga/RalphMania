<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'type', 'value', 'min_order_amount', 'max_discount_amount',
        'max_uses', 'used_count', 'max_uses_per_user', 'cooldown_days',
        'subscribers_only', 'is_active', 'starts_at', 'expires_at',
        'description_lv', 'description_en',
    ];

    protected $casts = [
        'value'              => 'decimal:2',
        'min_order_amount'   => 'decimal:2',
        'max_discount_amount'=> 'decimal:2',
        'subscribers_only'   => 'boolean',
        'is_active'          => 'boolean',
        'starts_at'          => 'datetime',
        'expires_at'         => 'datetime',
    ];

    // ─── ATTIECĪBAS ───────────────────────────────────────────────────────

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    // ─── TVĒRUMI ──────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            });
    }

    // ─── VALIDĀCIJA ──────────────────────────────────────────────────────────

    /**
     * Validē kuponu dotajam lietotājam + pasūtījuma summu
     * Atgriež ['valid' => true, 'discount' => 5.00] vai ['valid' => false, 'message' => '...']
     */
    public function validate(int $userId, float $orderAmount, string $locale = 'lv'): array
    {
        // Aktīvā pārbaude
        if (!$this->is_active) {
            return $this->invalid($locale === 'lv' ? 'Kupons nav aktīvs.' : 'Coupon is not active.');
        }

        // Datumu diapazons
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return $this->invalid($locale === 'lv' ? 'Kupons vēl nav derīgs.' : 'Coupon is not yet valid.');
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return $this->invalid($locale === 'lv' ? 'Kupona derīguma termiņš ir beidzies.' : 'Coupon has expired.');
        }

        // Globālais lietošanas ierobežojums
        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return $this->invalid($locale === 'lv' ? 'Kupons ir pilnībā nolietots.' : 'Coupon has reached its usage limit.');
        }

        // Minimālais pasūtījums
        if ($orderAmount < $this->min_order_amount) {
            return $this->invalid(
                $locale === 'lv'
                    ? "Minimālais pasūtījums šim kuponam: €{$this->min_order_amount}"
                    : "Minimum order for this coupon: €{$this->min_order_amount}"
            );
        }

        // Lietotāja lietojums + atdzišanas perioda pārbaude
        $lastUsage = $this->usages()
            ->where('user_id', $userId)
            ->latest('used_at')
            ->first();

        if ($lastUsage) {
            // Pārbauda, vai ir iekļauts atdzišanas periods
            if ($lastUsage->reusable_at && $lastUsage->reusable_at->isFuture()) {
                $daysLeft = (int) now()->diffInDays($lastUsage->reusable_at, false);
                $daysLeft = max(1, $daysLeft);
                return $this->invalid(
                    $locale === 'lv'
                        ? "Šo kuponu varēsi izmantot atkal pēc {$daysLeft} dienām."
                        : "You can use this coupon again in {$daysLeft} days."
                );
            }
        }

        // Tikai abonenti
        if ($this->subscribers_only) {
            $isSubscribed = NewsletterSubscriber::where('user_id', $userId)
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('subscription_expires_at')
                        ->orWhere('subscription_expires_at', '>=', now());
                })
                ->exists();

            if (!$isSubscribed) {
                return $this->invalid(
                    $locale === 'lv'
                        ? 'Šis kupons ir pieejams tikai jaunumu abonentiem.'
                        : 'This coupon is available to newsletter subscribers only.'
                );
            }
        }

        // Aprēķina atlaidi
        $discount = $this->calculateDiscount($orderAmount);

        return [
            'valid'    => true,
            'discount' => $discount,
            'message'  => $locale === 'lv'
                ? "Kupons pielietots! Atlaide: €{$discount}"
                : "Coupon applied! Discount: €{$discount}",
        ];
    }

    /**
     * Aprēķina faktisko atlaides summu
     */
    public function calculateDiscount(float $orderAmount): float
    {
        if ($this->type === 'percentage') {
            $discount = round($orderAmount * ($this->value / 100), 2);
        } else {
            // piefiksēta atlaide
            $discount = min((float) $this->value, $orderAmount);
        }

        // Piemēro maksimālo atlaides ierobežojumu
        if ($this->max_discount_amount !== null) {
            $discount = min($discount, (float) $this->max_discount_amount);
        }

        return $discount;
    }

    /**
     * Atzīmē kuponu kā lietotāja izmantotu un izsauc to, kad pasūtījums ir apstiprināts
     */
    public function markUsed(int $userId, int $orderId, float $discountAmount): CouponUsage
    {
        $this->increment('used_count');

        return $this->usages()->create([
            'user_id'         => $userId,
            'order_id'        => $orderId,
            'discount_amount' => $discountAmount,
            'used_at'         => now(),
            'reusable_at'     => now()->addDays($this->cooldown_days),
        ]);
    }

    // ─── PALĪGI ─────────────────────────────────────────────────────────────

    public function getFormattedValue(): string
    {
        return $this->type === 'percentage'
            ? "{$this->value}%"
            : "€{$this->value}";
    }

    public function getDescription(string $locale): string
    {
        return $locale === 'lv'
            ? ($this->description_lv ?? '')
            : ($this->description_en ?? '');
    }

    private function invalid(string $message): array
    {
        return ['valid' => false, 'message' => $message];
    }
}
