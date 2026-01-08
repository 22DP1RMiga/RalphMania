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
        }
        .header {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%)
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            background: #f9fafb;
            padding: 30px;
            border: 1px solid #e5e7eb;
            border-top: none;
        }
        .order-number {
            background: white;
            border: 2px dashed #ea6666;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin: 20px 0;
        }
        .order-number h2 {
            margin: 0;
            color: #ea6666;
            font-size: 24px;
        }
        .info-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #ea6666;
        }
        .info-box h3 {
            margin-top: 0;
            color: #ea6666;
            font-size: 16px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
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
        }
        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            margin: 20px 0;
            border-radius: 8px;
            overflow: hidden;
        }
        th {
            background: #ea6666;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        tr:last-child td {
            border-bottom: none;
        }
        .total-row {
            background: #f9fafb;
            font-weight: bold;
            font-size: 18px;
        }
        .total-row td {
            padding: 15px 12px;
            color: #ea6666;
        }
        .button {
            display: inline-block;
            background: #ea6666;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            margin: 20px 0;
        }
        .button:hover {
            background: #d35555;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-size: 14px;
            border-top: 2px solid #e5e7eb;
            margin-top: 30px;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        .highlight {
            background: #fef3c7;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #f59e0b;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>🎉 Paldies par pasūtījumu!</h1>
    <p style="margin: 10px 0 0 0; font-size: 16px;">Tavs pasūtījums ir veiksmīgi saņemts</p>
</div>

<div class="content">
    <div class="order-number">
        <p style="margin: 0 0 10px 0; color: #6b7280; font-size: 14px;">PASŪTĪJUMA NUMURS</p>
        <h2>{{ $order->order_number }}</h2>
        <p style="margin: 10px 0 0 0; color: #6b7280; font-size: 14px;">
            Datums: {{ $order->created_at->format('d.m.Y H:i') }}
        </p>
    </div>

    <div class="highlight">
        <strong>📧 Rēķins pievienots!</strong><br>
        Pasūtījuma rēķins ir pievienots šim e-pastam PDF formātā.
    </div>

    <div class="info-box">
        <h3>👤 Klienta informācija</h3>
        <div class="info-row">
            <span class="info-label">Vārds: </span>
            <span class="info-value">{{ $order->customer_name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">E-pasts: </span>
            <span class="info-value">{{ $order->customer_email }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Telefons: </span>
            <span class="info-value">{{ $order->customer_phone }}</span>
        </div>
    </div>

    <div class="info-box">
        <h3>🚚 Piegādes adrese</h3>
        <div class="info-row">
            <span class="info-label">Adrese: </span>
            <span class="info-value">{{ $order->delivery_address }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Pilsēta: </span>
            <span class="info-value">{{ $order->delivery_city }}, {{ $order->delivery_postal_code }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Valsts: </span>
            <span class="info-value">{{ $order->delivery_country }}</span>
        </div>
    </div>

    <h3 style="color: #ea6666; margin-top: 30px;">📦 Pasūtījuma saturs</h3>
    <table>
        <thead>
        <tr>
            <th>Produkts</th>
            <th style="text-align: center;">Skaits</th>
            <th style="text-align: right;">Cena</th>
            <th style="text-align: right;">Kopā</th>
        </tr>
        </thead>
        <tbody>
        @foreach($order->items as $item)
        <tr>
            <td>{{ $item->product_name }}</td>
            <td style="text-align: center;">{{ $item->quantity }}</td>
            <td style="text-align: right;">{{ number_format($item->price, 2) }} EUR</td>
            <td style="text-align: right;">{{ number_format($item->total, 2) }} EUR</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="3" style="text-align: right; font-weight: bold;">Starpsumma:</td>
            <td style="text-align: right;">{{ number_format($order->subtotal, 2) }} EUR</td>
        </tr>
        <tr>
            <td colspan="3" style="text-align: right; font-weight: bold;">Piegāde:</td>
            <td style="text-align: right;">{{ number_format($order->shipping_cost, 2) }} EUR</td>
        </tr>
        <tr class="total-row">
            <td colspan="3" style="text-align: right;">KOPĀ:</td>
            <td style="text-align: right;">{{ number_format($order->total_amount, 2) }} EUR</td>
        </tr>
        </tbody>
    </table>

    @if($order->payment)
    <div class="info-box">
        <h3>💳 Maksājuma informācija</h3>
        <div class="info-row">
            <span class="info-label">Metode: </span>
            <span class="info-value">
                    @if($order->payment->payment_method === 'card')
                        Bankas karte
                    @elseif($order->payment->payment_method === 'bank_transfer')
                        Bankas pārskaitījums
                    @elseif($order->payment->payment_method === 'cash_on_delivery')
                        Skaidra nauda pie saņemšanas
                    @endif
                </span>
        </div>
        <div class="info-row">
            <span class="info-label">Statuss: </span>
            <span class="info-value">
                    <span class="status-badge status-{{ $order->payment->status }}">
                        {{ ucfirst($order->payment->status) }}
                    </span>
                </span>
        </div>
    </div>
    @endif

    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ config('app.url') }}/orders/{{ $order->id }}" class="button">
            Skatīt pasūtījumu sistēmā
        </a>
    </div>

    <div class="highlight" style="background: #dbeafe; border-color: #f63b3b;">
        <strong>ℹ️ Kas notiek tālāk?</strong><br>
        1. Mēs saņēmām tavu pasūtījumu<br>
        2. Apstrādāsim to 1-2 darba dienu laikā<br>
        3. Nosūtīsim ar kurjeru<br>
        4. Saņemsi paziņojumu par piegādi
    </div>
</div>

<div class="footer">
    <p>
        <strong>RalphMania</strong><br>
        Brīvības iela 1, Rīga, LV-1010<br>
        📧 info@ralphmania.com | 📞 +371 20000000<br>
        <a href="http://www.ralphmania.com" style="color: #667eea;">www.ralphmania.com</a>
    </p>
    <p style="margin-top: 20px; font-size: 12px; color: #9ca3af;">
        Šis e-pasts tika nosūtīts, jo veici pasūtījumu RalphMania veikalā.<br>
        Ja domā, ka tas ir kļūda, lūdzu, sazinies ar mums.
    </p>
</div>
</body>
</html>
