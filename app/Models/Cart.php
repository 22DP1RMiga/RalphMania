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
     * Iegūst lietotāju, kuram pieder grozs
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Iegūst visas groza preces
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Iegūst vai izveido grozu pašreizējam lietotājam/sesijai
     *
     * KRITISKĀ METODE - to izsauc CartController
     */
    public static function getCurrentCart(): self
    {
        if (auth()->check()) {
            // Pieteicies lietotājs - izmanto user_id
            return self::firstOrCreate([
                'user_id' => auth()->id(),
            ]);
        } else {
            // Viesis - izmanto session_id
            $sessionId = session()->getId();

            // Sāk sesiju, ja tā vēl nav sākta
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
     * Pēc pieteikšanās apvieno viesa grozu ar lietotāja grozu
     */
    public static function mergeGuestCart(string $sessionId, int $userId): void
    {
        $guestCart = self::where('session_id', $sessionId)->first();

        if (!$guestCart) {
            return;
        }

        $userCart = self::firstOrCreate(['user_id' => $userId]);

        // Pārvieto preces no viesa groza uz lietotāja grozu
        foreach ($guestCart->items as $guestItem) {
            $existingItem = $userCart->items()
                ->where('product_id', $guestItem->product_id)
                ->first();

            if ($existingItem) {
                // Palielina daudzumu, ja prece jau pastāv
                $existingItem->increment('quantity', $guestItem->quantity);
            } else {
                // Pārvieto preci uz lietotāja grozu
                $guestItem->update(['cart_id' => $userCart->id]);
            }
        }

        // Dzēš viesu grozu
        $guestCart->delete();
    }

    /**
     * Iegūst kopējo vienumu skaitu
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Iegūst kopējo groza summu
     */
    public function getTotalAmountAttribute(): float
    {
        return (float) $this->items->sum(function ($item) {
            return $item->quantity * $item->price;
        });
    }

    /**
     * Notīra visas preces no groza
     */
    public function clearCart(): void
    {
        $this->items()->delete();
    }
}
