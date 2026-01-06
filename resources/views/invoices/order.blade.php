<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
        }

        .header {
            display: table;
            width: 100%;
            margin-bottom: 40px;
            border-bottom: 3px solid #dc2626;
            padding-bottom: 20px;
        }

        .header-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .header-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            text-align: right;
        }

        .logo {
            font-size: 28pt;
            font-weight: bold;
            color: #dc2626;
            margin-bottom: 10px;
        }

        .invoice-title {
            font-size: 24pt;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 10px;
        }

        .invoice-meta {
            font-size: 10pt;
            color: #6b7280;
        }

        .section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 12pt;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-grid {
            display: table;
            width: 100%;
        }

        .info-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 15px;
            background: #fef2f2;
            border: 1px solid #fecaca;
        }

        .info-label {
            font-weight: bold;
            color: #991b1b;
            font-size: 9pt;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .info-value {
            color: #1f2937;
            font-size: 10pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th {
            background: #dc2626;
            color: white;
            padding: 12px;
            text-align: left;
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        td {
            padding: 10px 12px;
            border-bottom: 1px solid #fecaca;
            font-size: 10pt;
        }

        tr:nth-child(even) {
            background: #fef2f2;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .totals {
            width: 100%;
            margin-top: 20px;
        }

        .totals-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }

        .totals-label {
            display: table-cell;
            text-align: right;
            padding-right: 20px;
            font-size: 11pt;
            color: #6b7280;
        }

        .totals-value {
            display: table-cell;
            text-align: right;
            font-size: 11pt;
            font-weight: bold;
            width: 150px;
        }

        .total-final {
            border-top: 2px solid #dc2626;
            padding-top: 10px;
            margin-top: 10px;
        }

        .total-final .totals-label {
            font-size: 14pt;
            color: #1f2937;
            font-weight: bold;
        }

        .total-final .totals-value {
            font-size: 16pt;
            color: #dc2626;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-confirmed {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-delivered {
            background: #d1fae5;
            color: #065f46;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #fecaca;
            text-align: center;
            font-size: 9pt;
            color: #6b7280;
        }

        .payment-info {
            background: #fef2f2;
            border: 1px solid #fecaca;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }

        .payment-method {
            font-weight: bold;
            color: #991b1b;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80pt;
            color: rgba(220, 38, 38, 0.05);
            font-weight: bold;
            z-index: -1;
        }
    </style>
</head>
<body>
<div class="watermark">RALPHMANIA</div>

<div class="container">
    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <div class="logo">{{ $company['name'] }}</div>
            <div style="font-size: 9pt; color: #6b7280;">
                {{ $company['address'] }}<br>
                {{ $company['city'] }}<br>
                {{ $company['country'] }}<br>
                Reģ. Nr: {{ $company['reg_number'] }}<br>
                PVN Nr: {{ $company['vat_number'] }}
            </div>
        </div>
        <div class="header-right">
            <div class="invoice-title">RĒĶINS</div>
            <div class="invoice-meta">
                <strong>Pasūtījums:</strong> {{ $order->order_number }}<br>
                <strong>Datums:</strong> {{ $order->created_at->format('d.m.Y') }}<br>
                <strong>Statuss:</strong>
                <span class="status-badge status-{{ $order->status }}">
                        {{ ucfirst($order->status) }}
                    </span>
            </div>
        </div>
    </div>

    <!-- Customer & Delivery Info -->
    <div class="section">
        <div class="section-title">Klienta un piegādes informācija</div>
        <div class="info-grid">
            <div class="info-col">
                <div class="info-label">Klients</div>
                <div class="info-value">
                    {{ $order->customer_name }}<br>
                    {{ $order->customer_email }}<br>
                    {{ $order->customer_phone }}
                </div>
            </div>
            <div class="info-col" style="border-left: none;">
                <div class="info-label">Piegādes adrese</div>
                <div class="info-value">
                    {{ $order->delivery_address }}<br>
                    {{ $order->delivery_city }}, {{ $order->delivery_postal_code }}<br>
                    {{ $order->delivery_country }}
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="section">
        <div class="section-title">Pasūtījuma saturs</div>
        <table>
            <thead>
            <tr>
                <th>Nr.</th>
                <th>Produkts</th>
                <th class="text-center">Daudzums</th>
                <th class="text-right">Cena</th>
                <th class="text-right">Kopā</th>
            </tr>
            </thead>
            <tbody>
            @foreach($order->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->product_name }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->price, 2) }} EUR</td>
                    <td class="text-right">{{ number_format($item->total, 2) }} EUR</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <!-- Totals -->
    <div class="totals">
        <div class="totals-row">
            <div class="totals-label">Starpsumma:</div>
            <div class="totals-value">{{ number_format($order->subtotal, 2) }} EUR</div>
        </div>
        <div class="totals-row">
            <div class="totals-label">Piegāde:</div>
            <div class="totals-value">{{ number_format($order->shipping_cost, 2) }} EUR</div>
        </div>
        <div class="totals-row">
            <div class="totals-label">PVN (21%):</div>
            <div class="totals-value">{{ number_format($order->total_amount * 0.21, 2) }} EUR</div>
        </div>
        <div class="totals-row total-final">
            <div class="totals-label">KOPĀ APMAKSAI:</div>
            <div class="totals-value">{{ number_format($order->total_amount, 2) }} EUR</div>
        </div>
    </div>

    <!-- Payment Info -->
    @if($order->payment)
        <div class="payment-info">
            <div style="font-size: 10pt;">
                <strong>Maksājuma veids:</strong>
                <span class="payment-method">
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
                <br>
                <strong>Maksājuma statuss:</strong>
                <span class="status-badge status-{{ $order->payment->status }}">
                    {{ ucfirst($order->payment->status) }}
                </span>
            </div>
        </div>
    @endif

    <!-- Notes -->
    @if($order->notes)
        <div class="section">
            <div class="section-title">Piezīmes</div>
            <div style="background: #fef2f2; padding: 15px; border: 1px solid #fecaca; border-radius: 4px;">
                {{ $order->notes }}
            </div>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>
            <strong style="color: #dc2626;">Paldies par pirkumu {{ $company['name'] }} veikalā!</strong><br>
            Jautājumu gadījumā sazinieties ar mums: {{ $company['email'] }} | {{ $company['phone'] }}<br>
            <strong>{{ $company['website'] }}</strong>
        </p>
    </div>
</div>
</body>
</html>
