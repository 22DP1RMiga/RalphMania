<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Carbon\Carbon;

class NewsletterSubscriber extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'token',
        'is_verified',
        'verified_at',
        'is_active',
        'unsubscribed_at',
        'receive_news',
        'receive_promotions',
        'receive_announcements',
        'subscription_expires_at', // NEW: abonēšanas termiņš
    ];

    protected $casts = [
        'is_verified'             => 'boolean',
        'verified_at'             => 'datetime',
        'is_active'               => 'boolean',
        'unsubscribed_at'         => 'datetime',
        'receive_news'            => 'boolean',
        'receive_promotions'      => 'boolean',
        'receive_announcements'   => 'boolean',
        'subscription_expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($subscriber) {
            $subscriber->token = Str::random(64);
        });
    }

    // ─── RELATIONSHIPS ───────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── SCOPES ──────────────────────────────────────────────────────────────

    /**
     * Active AND not expired subscribers.
     */
    public function scopeActive($query)
    {
        return $query
            ->where('is_active', true)
            ->where('is_verified', true)
            ->where(function ($q) {
                $q->whereNull('subscription_expires_at')
                    ->orWhere('subscription_expires_at', '>=', now());
            });
    }

    // ─── STATIC HELPERS ──────────────────────────────────────────────────────

    /**
     * Subscribe email. If user is logged in, auto-verify.
     * $durationDays: null = forever, integer = days until expiry.
     */
    public static function subscribe(
        string $email,
        ?int $userId = null,
        ?int $durationDays = null
    ): self {
        $expiresAt = $durationDays ? now()->addDays($durationDays) : null;

        return self::updateOrCreate(
            ['email' => $email],
            [
                'user_id'                 => $userId,
                'is_active'               => true,
                'is_verified'             => $userId ? true : false,
                'verified_at'             => $userId ? now() : null,
                'unsubscribed_at'         => null,
                'subscription_expires_at' => $expiresAt,
            ]
        );
    }

    public static function unsubscribeByToken(string $token): bool
    {
        $subscriber = self::where('token', $token)->first();
        if ($subscriber) {
            $subscriber->update([
                'is_active'       => false,
                'unsubscribed_at' => now(),
            ]);
            return true;
        }
        return false;
    }

    /**
     * Check if email is subscribed AND not expired.
     */
    public static function isSubscribed(string $email): bool
    {
        return self::where('email', $email)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('subscription_expires_at')
                    ->orWhere('subscription_expires_at', '>=', now());
            })
            ->exists();
    }

    // ─── ACCESSORS ───────────────────────────────────────────────────────────

    /**
     * Days remaining until expiry (null if no expiry).
     */
    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->subscription_expires_at) return null;
        $days = (int) now()->diffInDays($this->subscription_expires_at, false);
        return max(0, $days);
    }

    /**
     * True if subscription has expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        if (!$this->subscription_expires_at) return false;
        return $this->subscription_expires_at->isPast();
    }
}
