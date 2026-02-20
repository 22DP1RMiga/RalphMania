<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pasūtījuma apstiprinājums</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background: #f9fafb;
        }

        .header {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }

        .header h1 {
            margin: 0;
            font-size: 26px;
        }

        .header p {
            margin: 10px 0 0 0;
            font-size: 15px;
            opacity: 0.9;
        }

        .content {
            background: white;
            padding: 30px;
            border: 1px solid #fecaca;
            border-top: none;
            border-radius: 0 0 10px 10px;
        }

        .order-number {
            background: #fef2f2;
            border: 2px dashed #dc2626;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin: 20px 0;
        }

        .order-number p {
            margin: 0 0 8px 0;
            color: #6b7280;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .order-number h2 {
            margin: 0;
            color: #dc2626;
            font-size: 24px;
            font-family: monospace;
        }

        .order-number .order-date {
            margin: 8px 0 0 0;
            color: #6b7280;
            font-size: 13px;
        }

        .info-box {
            background: #fef2f2;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #dc2626;
        }

        .info-box h3 {
            margin-top: 0;
            margin-bottom: 12px;
            color: #991b1b;
            font-size: 15px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 7px 0;
            border-bottom: 1px solid #fecaca;
            font-size: 14px;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: bold;
            color: #6b7280;
        }

        .info-value {
            color: #1f2937;
            text-align: right;
        }

        /* Items table */
        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            margin: 16px 0;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #fecaca;
        }

        th {
            background: #dc2626;
            color: white;
            padding: 10px 12px;
            text-align: left;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
        }

        td {
            padding: 10px 12px;
            border-bottom: 1px solid #fef2f2;
            font-size: 13px;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:nth-child(even) td {
            background: #fef9f9;
        }

        .size-badge {
            display: inline-block;
            padding: 1px 7px;
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            color: #374151;
        }

        /* Totals */
        .totals-section {
            border: 1px solid #fecaca;
            border-radius: 8px;
            overflow: hidden;
            margin: 16px 0;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 9px 16px;
            font-size: 14px;
            border-bottom: 1px solid #fef2f2;
        }

        .totals-row:last-child {
            border-bottom: none;
        }

        .totals-row .label {
            color: #6b7280;
            font-weight: 500;
        }

        .totals-row .value {
            font-weight: 600;
            color: #1f2937;
        }

        .totals-row.discount {
            background: #f0fdf4;
            color: #059669;
        }

        .totals-row.discount .label,
        .totals-row.discount .value {
            color: #059669;
        }

        .coupon-badge {
            display: inline-block;
            padding: 1px 7px;
            background: #d1fae5;
            border: 1px solid #6ee7b7;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            color: #065f46;
            font-family: monospace;
            letter-spacing: 0.05em;
            margin-left: 6px;
        }

        .totals-row.total-final {
            background: #dc2626;
            padding: 14px 16px;
        }

        .totals-row.total-final .label,
        .totals-row.total-final .value {
            color: white;
            font-size: 16px;
            font-weight: bold;
        }

        /* Status badge */
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending { background: #fef3c7; color: #92400e; }
        .status-confirmed { background: #dbeafe; color: #1e40af; }
        .status-delivered { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }

        /* CTA button */
        .button {
            display: inline-block;
            background: #dc2626;
            color: white;
            padding: 14px 32px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 14px;
        }

        /* Highlight boxes */
        .highlight {
            padding: 14px 16px;
            border-radius: 8px;
            margin: 16px 0;
            font-size: 14px;
            line-height: 1.7;
        }

        .highlight-yellow {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            color: #78350f;
        }

        .highlight-blue {
            background: #dbeafe;
            border-left: 4px solid #3b82f6;
            color: #1e3a5f;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 24px 20px;
            color: #6b7280;
            font-size: 13px;
            border-top: 2px solid #fecaca;
            margin-top: 30px;
        }

        .footer strong {
            color: #dc2626;
        }

        .footer a {
            color: #dc2626;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>🎉 Paldies par pasūtījumu!</h1>
    <p>Tavs pasūtījums ir veiksmīgi saņemts</p>
</div>

<div class="content">

    {{-- Pasūtījuma numurs --}}
    <div class="order-number">
        <p>Pasūtījuma numurs</p>
        <h2>{{ $order->order_number }}</h2>
        <p class="order-date">{{ $order->created_at->format('d.m.Y H:i') }}</p>
    </div>

    {{-- Rēķins pievienots --}}
    <div class="highlight highlight-yellow">
        <strong>📧 Rēķins pievienots!</strong><br>
        Pasūtījuma rēķins ir pievienots šim e-pastam PDF formātā.
    </div>

    {{-- Klienta info --}}
    <div class="info-box">
        <h3>👤 Klienta informācija</h3>
        <div class="info-row">
            <span class="info-label">Vārds:</span>
            <span class="info-value">{{ $order->customer_name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">E-pasts:</span>
            <span class="info-value">{{ $order->customer_email }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Telefons:</span>
            <span class="info-value">{{ $order->customer_phone }}</span>
        </div>
    </div>

    {{-- Piegādes adrese --}}
    <div class="info-box">
        <h3>🚚 Piegādes adrese</h3>
        <div class="info-row">
            <span class="info-label">Adrese:</span>
            <span class="info-value">{{ $order->delivery_address }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Pilsēta:</span>
            <span class="info-value">{{ $order->delivery_city }}, {{ $order->delivery_postal_code }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Valsts:</span>
            <span class="info-value">{{ $order->delivery_country }}</span>
        </div>
    </div>

    {{-- Pasūtījuma produkti --}}
    <h3 style="color: #dc2626; margin: 24px 0 8px 0; font-size: 15px;">📦 Pasūtījuma saturs</h3>
    <table>
        <thead>
        <tr>
            <th>Produkts</th>
            <th style="text-align: center; width: 60px;">Izmērs</th>
            <th style="text-align: center; width: 50px;">Skaits</th>
            <th style="text-align: right; width: 80px;">Cena</th>
            <th style="text-align: right; width: 80px;">Kopā</th>
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

    {{-- Kopsavilkums --}}
    <div class="totals-section">
        <div class="totals-row">
            <span class="label">Starpsumma:</span>
            <span class="value">{{ number_format($order->subtotal, 2) }} €</span>
        </div>
        <div class="totals-row">
            <span class="label">Piegāde:</span>
            <span class="value">{{ number_format($order->shipping_cost, 2) }} €</span>
        </div>
        @if($order->discount_amount > 0)
            <div class="totals-row discount">
            <span class="label">
                🏷️ Atlaide
                @if($order->coupon_code)
                    <span class="coupon-badge">{{ $order->coupon_code }}</span>
                @endif
            </span>
                <span class="value">-{{ number_format($order->discount_amount, 2) }} €</span>
            </div>
        @endif
        <div class="totals-row total-final">
            <span class="label">KOPĀ APMAKSAI:</span>
            <span class="value">{{ number_format($order->total_amount, 2) }} €</span>
        </div>
    </div>

    {{-- Maksājuma info --}}
    @if($order->payment)
        <div class="info-box">
            <h3>💳 Maksājuma informācija</h3>
            <div class="info-row">
                <span class="info-label">Metode:</span>
                <span class="info-value">
                @if($order->payment->payment_method === 'card')
                        Bankas karte
                        @if($order->payment->card_last4)
                            (•••• {{ $order->payment->card_last4 }})
                        @endif
                    @elseif($order->payment->payment_method === 'bank_transfer')
                        Bankas pārskaitījums
                    @elseif($order->payment->payment_method === 'cash_on_delivery')
                        Skaidra nauda pie saņemšanas
                    @endif
            </span>
            </div>
            <div class="info-row">
                <span class="info-label">Statuss:</span>
                <span class="info-value">
                <span class="status-badge status-{{ $order->payment->status }}">
                    {{ ucfirst($order->payment->status) }}
                </span>
            </span>
            </div>
        </div>
    @endif

    {{-- CTA poga --}}
    <div style="text-align: center; margin: 28px 0 20px 0;">
        <a href="{{ config('app.url') }}/orders/{{ $order->id }}" class="button">
            Skatīt pasūtījumu sistēmā →
        </a>
    </div>

    {{-- Kas notiek tālāk --}}
    <div class="highlight highlight-blue">
        <strong>ℹ️ Kas notiek tālāk?</strong><br>
        1. Mēs saņēmām tavu pasūtījumu<br>
        2. Apstrādāsim to 1–2 darba dienu laikā<br>
        3. Nosūtīsim ar kurjeru<br>
        4. Saņemsi paziņojumu par piegādi
    </div>

</div>

<div class="footer">
    <p>
        <strong>RalphMania</strong><br>
        Brīvības iela 1, Rīga, LV-1010<br>
        📧 ralphmania.roltonslv@gmail.com | 📞 +371 20000000<br>
        <a href="http://www.ralphmania.com">www.ralphmania.com</a>
    </p>
    <p style="margin-top: 16px; font-size: 12px; color: #9ca3af;">
        Šis e-pasts tika nosūtīts, jo veici pasūtījumu RalphMania veikalā.<br>
        Ja domā, ka tas ir kļūda, lūdzu, sazinies ar mums.
    </p>
</div>

</body>
</html>
