<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
    ];

    protected $appends = [
        'total_items',
        'total_amount',
    ];

    /**
     * Get the user that owns the cart
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all cart items
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get or create cart for current user/session
     *
     * CRITICAL METHOD - This is called by CartController
     */
    public static function getCurrentCart(): self
    {
        if (auth()->check()) {
            // Logged in user - use user_id
            return self::firstOrCreate([
                'user_id' => auth()->id(),
            ]);
        } else {
            // Guest - use session_id
            $sessionId = session()->getId();

            // Start session if not started
            if (!$sessionId) {
                session()->start();
                $sessionId = session()->getId();
            }

            return self::firstOrCreate([
                'session_id' => $sessionId,
            ]);
        }
    }

    /**
     * Merge guest cart with user cart after login
     */
    public static function mergeGuestCart(string $sessionId, int $userId): void
    {
        $guestCart = self::where('session_id', $sessionId)->first();

        if (!$guestCart) {
            return;
        }

        $userCart = self::firstOrCreate(['user_id' => $userId]);

        // Move items from guest cart to user cart
        foreach ($guestCart->items as $guestItem) {
            $existingItem = $userCart->items()
                ->where('product_id', $guestItem->product_id)
                ->first();

            if ($existingItem) {
                // Increase quantity if item already exists
                $existingItem->increment('quantity', $guestItem->quantity);
            } else {
                // Move item to user cart
                $guestItem->update(['cart_id' => $userCart->id]);
            }
        }

        // Delete guest cart
        $guestCart->delete();
    }

    /**
     * Get total items count
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Get total cart amount
     */
    public function getTotalAmountAttribute(): float
    {
        return (float) $this->items->sum(function ($item) {
            return $item->quantity * $item->price;
        });
    }

    /**
     * Clear all items from cart
     */
    public function clearCart(): void
    {
        $this->items()->delete();
    }
}
