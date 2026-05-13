<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * Nav laika zīmogu pasūtījuma vienībām (datubāzē tikai created_at)
     */
    public $timestamps = false;

    /**
     * Atribūti, kurus var piešķirt masveidā
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'quantity',
        'price',
        // Piezīme: “total” ir ĢENERĒTA kolonna datubāzē (daudzums * cena)
    ];

    /**
     * Atribūti, kas jāpielieto
     */
    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
    ];

    /**
     * Piekļuves elementi, kas jāpievieno modeļa masīva formai
     */
    protected $appends = [
        'total',
    ];

    /**
     * Iegūst secību, kurai pieder šis vienums
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Iegūst produktu (oriģinālo produkta atsauci)
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Aprēķina kopsummu (quantity × price)
     *
     * Tas atbilst datubāzes ĢENERĒTAI kolonnai
     */
    public function getTotalAttribute(): float
    {
        // Ja 'total' eksistē atribūtos (no DB), izmanto to
        if (isset($this->attributes['total'])) {
            return (float) $this->attributes['total'];
        }

        // Citādi aprēķina
        return (float) ($this->quantity * $this->price);
    }
}
