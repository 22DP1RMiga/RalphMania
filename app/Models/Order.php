<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'delivery_country',
        'delivery_city',
        'delivery_address',
        'delivery_postal_code',
        'subtotal',
        'shipping_cost',
        'discount_amount',
        'coupon_code',
        'total_amount',
        'status',
        'notes',
        'tracking_number',
        'paid_at',
        'shipped_at',
        'delivered_at',
    ];

    protected $casts = [
        'subtotal'        => 'decimal:2',
        'shipping_cost'   => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount'    => 'decimal:2',
        'paid_at'         => 'datetime',
        'shipped_at'      => 'datetime',
        'delivered_at'    => 'datetime',
    ];

    protected $appends = [
        'status_color',
        'can_be_cancelled',
    ];

    // ─── ATTIECĪBAS ───────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function courierAssignments(): HasMany
    {
        return $this->hasMany(CourierAssignment::class);
    }

    // ─── AKSESUĀRI ───────────────────────────────────────────────────────────

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending'    => 'yellow',
            'confirmed'  => 'blue',
            'processing' => 'indigo',
            'packed'     => 'purple',
            'shipped'    => 'orange',
            'in_transit' => 'teal',
            'delivered'  => 'green',
            'cancelled'  => 'red',
            'refunded'   => 'gray',
            default      => 'gray',
        };
    }

    public function getCanBeCancelledAttribute(): bool
    {
        return !in_array($this->status, [
            'shipped', 'in_transit', 'delivered', 'cancelled', 'refunded'
        ]);
    }

    public function getStatusLabelLvAttribute(): string
    {
        return match($this->status) {
            'pending'    => 'Gaida apstiprinājumu',
            'confirmed'  => 'Apstiprināts',
            'processing' => 'Apstrādē',
            'packed'     => 'Iepakots',
            'shipped'    => 'Nosūtīts',
            'in_transit' => 'Ceļā',
            'delivered'  => 'Piegādāts',
            'cancelled'  => 'Atcelts',
            'refunded'   => 'Atmaksāts',
            default      => 'Nezināms',
        };
    }

    public function getStatusLabelEnAttribute(): string
    {
        return match($this->status) {
            'pending'    => 'Pending',
            'confirmed'  => 'Confirmed',
            'processing' => 'Processing',
            'packed'     => 'Packed',
            'shipped'    => 'Shipped',
            'in_transit' => 'In Transit',
            'delivered'  => 'Delivered',
            'cancelled'  => 'Cancelled',
            'refunded'   => 'Refunded',
            default      => 'Unknown',
        };
    }
}
