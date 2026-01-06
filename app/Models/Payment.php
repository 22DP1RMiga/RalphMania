<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'order_id',
        'payment_method',
        'card_last4',
        'card_brand',
        'card_exp_month',
        'card_exp_year',
        'amount',
        'currency',
        'status',
        'transaction_id',
        'gateway_response',
        'paid_at',
        'refunded_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    /**
     * Get the order this payment belongs to
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Mark payment as completed
     *
     * @param string|null $transactionId Payment gateway transaction ID
     * @param array|null $gatewayResponse Full gateway response data
     */
    public function markAsCompleted(string $transactionId = null, array $gatewayResponse = null): void
    {
        $this->update([
            'status' => 'completed',
            'transaction_id' => $transactionId,
            'gateway_response' => $gatewayResponse,
            'paid_at' => now(),
        ]);

        // Update order status to confirmed
        $this->order->update([
            'status' => 'confirmed',
            'paid_at' => now(),
        ]);
    }

    /**
     * Mark payment as failed
     *
     * @param array|null $gatewayResponse Error response from gateway
     */
    public function markAsFailed(array $gatewayResponse = null): void
    {
        $this->update([
            'status' => 'failed',
            'gateway_response' => $gatewayResponse,
        ]);
    }

    /**
     * Process refund
     */
    public function processRefund(): void
    {
        $this->update([
            'status' => 'refunded',
            'refunded_at' => now(),
        ]);

        // Update order status
        $this->order->update([
            'status' => 'refunded',
        ]);
    }

    /**
     * Get payment method label
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return match($this->payment_method) {
            'card' => 'Credit/Debit Card',
            'paypal' => 'PayPal',
            'bank_transfer' => 'Bank Transfer',
            'cash_on_delivery' => 'Cash on Delivery',
            default => ucfirst($this->payment_method),
        };
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'completed' => 'green',
            'failed' => 'red',
            'refunded' => 'gray',
            default => 'gray',
        };
    }
}
