<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

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
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'is_active' => 'boolean',
        'unsubscribed_at' => 'datetime',
        'receive_news' => 'boolean',
        'receive_promotions' => 'boolean',
        'receive_announcements' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($subscriber) {
            // Ģenerēt unikālu tokenu
            $subscriber->token = Str::random(64);
        });
    }

    /**
     * Get the user that owns the subscription.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for active subscribers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('is_verified', true);
    }

    /**
     * Subscribe a new email.
     */
    public static function subscribe(string $email, ?int $userId = null): self
    {
        return self::updateOrCreate(
            ['email' => $email],
            [
                'user_id' => $userId,
                'is_active' => true,
                'is_verified' => $userId ? true : false, // Auto-verify if logged in
                'verified_at' => $userId ? now() : null,
                'unsubscribed_at' => null,
            ]
        );
    }

    /**
     * Unsubscribe by token.
     */
    public static function unsubscribeByToken(string $token): bool
    {
        $subscriber = self::where('token', $token)->first();

        if ($subscriber) {
            $subscriber->update([
                'is_active' => false,
                'unsubscribed_at' => now(),
            ]);
            return true;
        }

        return false;
    }

    /**
     * Check if email is subscribed.
     */
    public static function isSubscribed(string $email): bool
    {
        return self::where('email', $email)
            ->where('is_active', true)
            ->exists();
    }
}
