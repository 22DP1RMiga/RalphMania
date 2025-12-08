<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'customer_name',
        'customer_email',
        'customer_phone',
        'delivery_country',
        'delivery_city',
        'delivery_address',
        'delivery_postal_code',
        'subtotal',
        'shipping_cost',
        'total_amount',
        'notes',
        'tracking_number',
        'paid_at',
        'shipped_at',
        'delivered_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    // Helper methods
    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    public static function generateOrderNumber()
    {
        $prefix = 'RM';
        $year = date('Y');
        $lastOrder = self::whereYear('created_at', date('Y'))
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastOrder ? intval(substr($lastOrder->order_number, -5)) + 1 : 1;

        return sprintf('%s-%s-%05d', $prefix, $year, $number);
    }

    // Status helpers
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isDelivered()
    {
        return $this->status === 'delivered';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }
}
