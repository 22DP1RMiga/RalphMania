<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('email.order.subject') }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background: #f9fafb; }
        .header { background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .header h1 { margin: 0; font-size: 26px; }
        .header p { margin: 10px 0 0; font-size: 15px; opacity: 0.9; }
        .content { background: white; padding: 30px; border: 1px solid #fecaca; border-top: none; border-radius: 0 0 10px 10px; }
        .order-number { background: #fef2f2; border: 2px dashed #dc2626; padding: 20px; text-align: center; border-radius: 8px; margin: 20px 0; }
        .order-number p { margin: 0 0 8px; color: #6b7280; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; }
        .order-number h2 { margin: 0; color: #dc2626; font-size: 24px; font-family: monospace; }
        .order-number .order-date { margin: 8px 0 0; color: #6b7280; font-size: 13px; }
        .info-box { background: #fef2f2; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #dc2626; }
        .info-box h3 { margin-top: 0; margin-bottom: 12px; color: #991b1b; font-size: 15px; }
        .info-row { display: flex; justify-content: space-between; padding: 7px 0; border-bottom: 1px solid #fecaca; font-size: 14px; }
        .info-row:last-child { border-bottom: none; }
        .info-label { font-weight: bold; color: #6b7280; }
        .info-value { color: #1f2937; text-align: right; }
        table { width: 100%; background: white; border-collapse: collapse; margin: 16px 0; border-radius: 8px; overflow: hidden; border: 1px solid #fecaca; }
        th { background: #dc2626; color: white; padding: 10px 12px; text-align: left; font-weight: bold; font-size: 12px; text-transform: uppercase; }
        td { padding: 10px 12px; border-bottom: 1px solid #fef2f2; font-size: 13px; }
        tr:last-child td { border-bottom: none; }
        tr:nth-child(even) td { background: #fef9f9; }
        .size-badge { display: inline-block; padding: 1px 7px; background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 3px; font-size: 11px; font-weight: bold; color: #374151; }
        .totals-section { border: 1px solid #fecaca; border-radius: 8px; overflow: hidden; margin: 16px 0; }
        .totals-row { display: flex; justify-content: space-between; padding: 9px 16px; font-size: 14px; border-bottom: 1px solid #fef2f2; }
        .totals-row:last-child { border-bottom: none; }
        .totals-row .label { color: #6b7280; font-weight: 500; }
        .totals-row .value { font-weight: 600; color: #1f2937; }
        .totals-row.discount { background: #f0fdf4; }
        .totals-row.discount .label, .totals-row.discount .value { color: #059669; }
        .coupon-badge { display: inline-block; padding: 1px 7px; background: #d1fae5; border: 1px solid #6ee7b7; border-radius: 3px; font-size: 11px; font-weight: bold; color: #065f46; font-family: monospace; margin-left: 6px; }
        .totals-row.total-final { background: #dc2626; padding: 14px 16px; }
        .totals-row.total-final .label, .totals-row.total-final .value { color: white; font-size: 16px; font-weight: bold; }
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-confirmed { background: #dbeafe; color: #1e40af; }
        .status-delivered { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .button { display: inline-block; background: #dc2626; color: white; padding: 14px 32px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 14px; }
        .highlight { padding: 14px 16px; border-radius: 8px; margin: 16px 0; font-size: 14px; line-height: 1.7; }
        .highlight-yellow { background: #fef3c7; border-left: 4px solid #f59e0b; color: #78350f; }
        .highlight-blue { background: #dbeafe; border-left: 4px solid #3b82f6; color: #1e3a5f; }
        .footer { text-align: center; padding: 24px 20px; color: #6b7280; font-size: 13px; border-top: 2px solid #fecaca; margin-top: 30px; }
        .footer strong { color: #dc2626; }
        .footer a { color: #dc2626; }
    </style>
</head>
<body>

<div class="header">
    <h1>🎉 {{ __('email.order.title') }}</h1>
    <p>{{ __('email.order.subtitle') }}</p>
</div>

<div class="content">
    <div class="order-number">
        <p>{{ __('email.order.number_label') }}</p>
        <h2>{{ $order->order_number }}</h2>
        <p class="order-date">{{ $order->created_at->format('d.m.Y H:i') }}</p>
    </div>

    <div class="highlight highlight-yellow">
        <strong>📧 {{ __('email.order.invoice_notice') }}</strong><br>
        {{ __('email.order.invoice_text') }}
    </div>

    <div class="info-box">
        <h3>👤 {{ __('email.order.customer_info') }}</h3>
        <div class="info-row">
            <span class="info-label">{{ __('email.order.name_label') }}</span>
            <span class="info-value">{{ $order->customer_name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">{{ __('email.order.email_label') }}</span>
            <span class="info-value">{{ $order->customer_email }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">{{ __('email.order.phone_label') }}</span>
            <span class="info-value">{{ $order->customer_phone }}</span>
        </div>
    </div>

    <div class="info-box">
        <h3>🚚 {{ __('email.order.delivery_address') }}</h3>
        <div class="info-row">
            <span class="info-label">{{ __('email.order.address_label') }}</span>
            <span class="info-value">{{ $order->delivery_address }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">{{ __('email.order.city_label') }}</span>
            <span class="info-value">{{ $order->delivery_city }}, {{ $order->delivery_postal_code }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">{{ __('email.order.country_label') }}</span>
            <span class="info-value">{{ $order->delivery_country }}</span>
        </div>
    </div>

    <h3 style="color: #dc2626; margin: 24px 0 8px; font-size: 15px;">📦 {{ __('email.order.items_title') }}</h3>
    <table>
        <thead>
        <tr>
            <th>{{ __('email.order.product') }}</th>
            <th style="text-align: center; width: 60px;">{{ __('email.order.size') }}</th>
            <th style="text-align: center; width: 50px;">{{ __('email.order.qty') }}</th>
            <th style="text-align: right; width: 80px;">{{ __('email.order.price') }}</th>
            <th style="text-align: right; width: 80px;">{{ __('email.order.total') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($order->items as $item)
            <tr>
                <td>{{ $item->product_name }}</td>
                <td style="text-align: center;">
                    @if($item->size)
                        <span class="size-badge">{{ $item->size }}</span>
                    @else
                        <span style="color: #d1d5db;">—</span>
                    @endif
                </td>
                <td style="text-align: center;">{{ $item->quantity }}</td>
                <td style="text-align: right;">{{ number_format($item->price, 2) }} €</td>
                <td style="text-align: right;">{{ number_format($item->total, 2) }} €</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="totals-section">
        <div class="totals-row">
            <span class="label">{{ __('email.order.subtotal') }}</span>
            <span class="value">{{ number_format($order->subtotal, 2) }} €</span>
        </div>
        <div class="totals-row">
            <span class="label">{{ __('email.order.shipping') }}</span>
            <span class="value">{{ number_format($order->shipping_cost, 2) }} €</span>
        </div>
        @if($order->discount_amount > 0)
            <div class="totals-row discount">
                <span class="label">
                    🏷️ {{ __('email.order.discount') }}
                    @if($order->coupon_code)
                        <span class="coupon-badge">{{ $order->coupon_code }}</span>
                    @endif
                </span>
                <span class="value">-{{ number_format($order->discount_amount, 2) }} €</span>
            </div>
        @endif
        <div class="totals-row total-final">
            <span class="label">{{ __('email.order.grand_total') }}</span>
            <span class="value">{{ number_format($order->total_amount, 2) }} €</span>
        </div>
    </div>

    @if($order->payment)
        <div class="info-box">
            <h3>💳 {{ __('email.order.payment_info') }}</h3>
            <div class="info-row">
                <span class="info-label">{{ __('email.order.payment_method') }}</span>
                <span class="info-value">
                    @if($order->payment->payment_method === 'card')
                        {{ __('email.order.payment_card') }}
                        @if($order->payment->card_last4) (•••• {{ $order->payment->card_last4 }}) @endif
                    @elseif($order->payment->payment_method === 'bank_transfer')
                        {{ __('email.order.payment_bank') }}
                    @elseif($order->payment->payment_method === 'cash_on_delivery')
                        {{ __('email.order.payment_cash') }}
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">{{ __('email.order.payment_status') }}</span>
                <span class="info-value">
                    <span class="status-badge status-{{ $order->payment->status }}">{{ ucfirst($order->payment->status) }}</span>
                </span>
            </div>
        </div>
    @endif

    <div style="text-align: center; margin: 28px 0 20px;">
        <a href="{{ config('app.url') }}/orders/{{ $order->id }}" class="button">{{ __('email.order.view_order') }}</a>
    </div>

    <div class="highlight highlight-blue">
        <strong>ℹ️ {{ __('email.order.whats_next') }}</strong><br>
        1. {{ __('email.order.next_1') }}<br>
        2. {{ __('email.order.next_2') }}<br>
        3. {{ __('email.order.next_3') }}<br>
        4. {{ __('email.order.next_4') }}
    </div>
</div>

<div class="footer">
    <p>
        <strong>RalphMania</strong><br>
        Brīvības iela 1, Rīga, LV-1010<br>
        📧 ralphmania.roltonslv@gmail.com | 📞 +371 20000000<br>
        <a href="{{ config('app.url') }}">{{ config('app.url') }}</a>
    </p>
    <p style="margin-top: 16px; font-size: 12px; color: #9ca3af;">
        {{ __('email.footer_auto_sent') }}<br>
        © {{ date('Y') }} RalphMania. {{ __('email.footer_rights') }}
    </p>
</div>

</body>
</html>
