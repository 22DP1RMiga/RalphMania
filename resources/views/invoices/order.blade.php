<!DOCTYPE html>
<html lang="{{ $order->delivery_country === 'Latvia' ? 'lv' : 'en' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        {{ $order->delivery_country === 'Latvia' ? 'Rēķins' : 'Invoice' }}
        — {{ $order->order_number }}
    </title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            color: #333;
            line-height: 1.6;
        }

        .container { max-width: 800px; margin: 0 auto; padding: 40px; }

        /* ── HEADER ──────────────────────────────────────────────── */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 40px;
            border-bottom: 3px solid #dc2626;
            padding-bottom: 20px;
        }
        .header-left  { display: table-cell; width: 50%; vertical-align: top; }
        .header-right { display: table-cell; width: 50%; vertical-align: top; text-align: right; }

        .logo { font-size: 28pt; font-weight: bold; color: #dc2626; margin-bottom: 10px; }

        .invoice-title { font-size: 24pt; font-weight: bold; color: #1f2937; margin-bottom: 10px; }
        .invoice-meta  { font-size: 10pt; color: #6b7280; }

        /* ── SECTIONS ────────────────────────────────────────────── */
        .section { margin-bottom: 30px; }
        .section-title {
            font-size: 12pt; font-weight: bold; color: #1f2937;
            margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px;
        }

        /* ── INFO GRID ───────────────────────────────────────────── */
        .info-grid { display: table; width: 100%; }
        .info-col {
            display: table-cell; width: 50%; vertical-align: top;
            padding: 15px; background: #fef2f2; border: 1px solid #fecaca;
        }
        .info-label { font-weight: bold; color: #991b1b; font-size: 9pt; text-transform: uppercase; margin-bottom: 5px; }
        .info-value { color: #1f2937; font-size: 10pt; }

        /* ── TABLE ───────────────────────────────────────────────── */
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th {
            background: #dc2626; color: white;
            padding: 12px; text-align: left;
            font-size: 10pt; font-weight: bold; text-transform: uppercase;
        }
        td { padding: 10px 12px; border-bottom: 1px solid #fecaca; font-size: 10pt; }
        tr:nth-child(even) { background: #fef2f2; }
        tr:last-child td { border-bottom: none; }
        .text-right  { text-align: right; }
        .text-center { text-align: center; }

        /* ── TOTALS ──────────────────────────────────────────────── */
        .totals { width: 100%; margin-top: 20px; }

        .totals-row { display: table; width: 100%; margin-bottom: 8px; }
        .totals-label { display: table-cell; text-align: right; padding-right: 20px; font-size: 11pt; color: #6b7280; }
        .totals-value { display: table-cell; text-align: right; font-size: 11pt; font-weight: bold; width: 150px; }

        /* PVN rinda — mazāka, pelēkāka, kursīvs */
        .vat-included-row .totals-label { font-size: 9pt; color: #9ca3af; font-style: italic; }
        .vat-included-row .totals-value { font-size: 9pt; color: #9ca3af; font-weight: normal; }

        /* Bezmaksas piegāde */
        .shipping-free .totals-value { color: #059669; }

        /* Atlaide */
        .discount-row .totals-label { color: #059669; }
        .discount-value { color: #059669; }

        .coupon-badge {
            display: inline-block; padding: 1px 7px;
            background: #d1fae5; border: 1px solid #6ee7b7;
            border-radius: 3px; font-size: 8pt; font-weight: bold;
            color: #065f46; font-family: monospace; letter-spacing: 0.05em; margin: 0 3px;
        }

        /* Gala summa */
        .total-final { border-top: 2px solid #dc2626; padding-top: 10px; margin-top: 10px; }
        .total-final .totals-label { font-size: 14pt; color: #1f2937; font-weight: bold; }
        .total-final .totals-value { font-size: 16pt; color: #dc2626; }

        /* ── STATUS BADGE ─────────────────────────────────────────── */
        .status-badge {
            display: inline-block; padding: 4px 12px;
            border-radius: 4px; font-size: 9pt; font-weight: bold; text-transform: uppercase;
        }
        .status-pending    { background: #fef3c7; color: #92400e; }
        .status-confirmed  { background: #dbeafe; color: #1e40af; }
        .status-processing { background: #e0e7ff; color: #3730a3; }
        .status-packed     { background: #ede9fe; color: #5b21b6; }
        .status-shipped    { background: #ffedd5; color: #9a3412; }
        .status-in_transit { background: #ccfbf1; color: #065f46; }
        .status-delivered  { background: #d1fae5; color: #065f46; }
        .status-cancelled  { background: #fee2e2; color: #991b1b; }
        .status-refunded   { background: #f3f4f6; color: #374151; }

        /* ── PAYMENT ─────────────────────────────────────────────── */
        .payment-info {
            background: #fef2f2; border: 1px solid #fecaca;
            padding: 15px; margin: 20px 0; border-radius: 4px;
        }
        .payment-method { font-weight: bold; color: #991b1b; }

        /* ── FOOTER ──────────────────────────────────────────────── */
        .footer {
            margin-top: 50px; padding-top: 20px;
            border-top: 2px solid #fecaca;
            text-align: center; font-size: 9pt; color: #6b7280;
        }

        /* ── WATERMARK ───────────────────────────────────────────── */
        .watermark {
            position: fixed; top: 50%; left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80pt; color: rgba(220, 38, 38, 0.05);
            font-weight: bold; z-index: -1;
        }
    </style>
</head>
<body>

{{-- ════════════════════════════════════════════════════════════════
     VALODAS MAINĪGIE
     Latvija → LV, visi pārējie → EN
     Cenas datubāzē ir BRUTO (ar PVN iekļautu).
     PVN = subtotal × 21 / 121  (NEvis × 0.21)
     ════════════════════════════════════════════════════════════════ --}}
@php
    $isLv = $order->delivery_country === 'Latvia';

    // ── PVN APRĒĶINS (t.sk. no bruto) ────────────────────────────
    $vatRate     = 21; // %
    $vatAmount   = round($order->subtotal * $vatRate / (100 + $vatRate), 2);
    $exVat       = round($order->subtotal - $vatAmount, 2);

    // ── STATUSA TULKOJUMS ─────────────────────────────────────────
    $statusLabels = [
        'pending'    => ['lv' => 'Gaida apstiprinājumu', 'en' => 'Pending'],
        'confirmed'  => ['lv' => 'Apstiprināts',         'en' => 'Confirmed'],
        'processing' => ['lv' => 'Apstrādē',             'en' => 'Processing'],
        'packed'     => ['lv' => 'Iepakots',             'en' => 'Packed'],
        'shipped'    => ['lv' => 'Nosūtīts',             'en' => 'Shipped'],
        'in_transit' => ['lv' => 'Ceļā',                 'en' => 'In Transit'],
        'delivered'  => ['lv' => 'Piegādāts',            'en' => 'Delivered'],
        'cancelled'  => ['lv' => 'Atcelts',              'en' => 'Cancelled'],
        'refunded'   => ['lv' => 'Atmaksāts',            'en' => 'Refunded'],
    ];
    $statusLabel = $statusLabels[$order->status][$isLv ? 'lv' : 'en'] ?? ucfirst($order->status);

    // ── MAKSĀJUMA VEIDA TULKOJUMS ─────────────────────────────────
    $paymentLabels = [
        'card'             => ['lv' => 'Bankas karte',                  'en' => 'Credit/Debit Card'],
        'bank_transfer'    => ['lv' => 'Bankas pārskaitījums',          'en' => 'Bank Transfer'],
        'cash_on_delivery' => ['lv' => 'Skaidra nauda pie saņemšanas', 'en' => 'Cash on Delivery'],
    ];
    $paymentLabel = $order->payment
        ? ($paymentLabels[$order->payment->payment_method][$isLv ? 'lv' : 'en'] ?? $order->payment->payment_method)
        : null;

    // ── MAKSĀJUMA STATUSA TULKOJUMS ───────────────────────────────
    $paymentStatusLabels = [
        'pending'   => ['lv' => 'Gaida',      'en' => 'Pending'],
        'completed' => ['lv' => 'Apmaksāts',  'en' => 'Completed'],
        'failed'    => ['lv' => 'Neizdevās',  'en' => 'Failed'],
        'refunded'  => ['lv' => 'Atmaksāts',  'en' => 'Refunded'],
    ];
    $paymentStatusLabel = $order->payment
        ? ($paymentStatusLabels[$order->payment->status][$isLv ? 'lv' : 'en'] ?? ucfirst($order->payment->status))
        : null;
@endphp

<div class="watermark">RALPHMANIA</div>

<div class="container">

    {{-- ── GALVENE ─────────────────────────────────────────────── --}}
    <div class="header">
        <div class="header-left">
            <div class="logo">{{ $company['name'] }}</div>
            <div style="font-size: 9pt; color: #6b7280; line-height: 1.8;">
                {{ $company['address'] }}<br>
                {{ $company['city'] }}<br>
                {{ $company['country'] }}<br>
                {{ $isLv ? 'Reģ. Nr' : 'Reg. No' }}: {{ $company['reg_number'] }}<br>
                {{ $isLv ? 'PVN Nr' : 'VAT No' }}: {{ $company['vat_number'] }}
            </div>
        </div>
        <div class="header-right">
            <div class="invoice-title">{{ $isLv ? 'RĒĶINS' : 'INVOICE' }}</div>
            <div class="invoice-meta" style="line-height: 2;">
                <strong>{{ $isLv ? 'Pasūtījums' : 'Order' }}:</strong> {{ $order->order_number }}<br>
                <strong>{{ $isLv ? 'Datums' : 'Date' }}:</strong> {{ $order->created_at->format('d.m.Y') }}<br>
                <strong>{{ $isLv ? 'Statuss' : 'Status' }}:</strong>
                <span class="status-badge status-{{ $order->status }}">{{ $statusLabel }}</span>
            </div>
        </div>
    </div>

    {{-- ── KLIENTA UN PIEGĀDES INFO ────────────────────────────── --}}
    <div class="section">
        <div class="section-title">
            {{ $isLv ? 'Klienta un piegādes informācija' : 'Customer & Delivery Information' }}
        </div>
        <div class="info-grid">
            <div class="info-col">
                <div class="info-label">{{ $isLv ? 'Klients' : 'Customer' }}</div>
                <div class="info-value">
                    {{ $order->customer_name }}<br>
                    {{ $order->customer_email }}<br>
                    {{ $order->customer_phone }}
                </div>
            </div>
            <div class="info-col" style="border-left: none;">
                <div class="info-label">{{ $isLv ? 'Piegādes adrese' : 'Delivery Address' }}</div>
                <div class="info-value">
                    {{ $order->delivery_address }}<br>
                    {{ $order->delivery_city }}@if($order->delivery_postal_code), {{ $order->delivery_postal_code }}@endif<br>
                    {{ $order->delivery_country }}
                </div>
            </div>
        </div>
    </div>

    {{-- ── PRODUKTU TABULA ─────────────────────────────────────── --}}
    <div class="section">
        <div class="section-title">
            {{ $isLv ? 'Pasūtījuma saturs' : 'Order Contents' }}
        </div>
        <table>
            <thead>
            <tr>
                <th class="text-center" style="width: 35px;">{{ $isLv ? 'Nr.' : 'No.' }}</th>
                <th>{{ $isLv ? 'Produkts' : 'Product' }}</th>
                <th class="text-center" style="width: 65px;">{{ $isLv ? 'Izmērs' : 'Size' }}</th>
                <th class="text-center" style="width: 75px;">{{ $isLv ? 'Daudz.' : 'Qty' }}</th>
                <th class="text-right" style="width: 90px;">{{ $isLv ? 'Vienības cena' : 'Unit Price' }}</th>
                <th class="text-right" style="width: 90px;">{{ $isLv ? 'Kopā' : 'Total' }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($order->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->product_name }}</td>
                    <td class="text-center">{{ $item->size ?? '—' }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->price, 2) }} EUR</td>
                    <td class="text-right">{{ number_format($item->total, 2) }} EUR</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    {{-- ── SUMMAS ──────────────────────────────────────────────── --}}
    <div class="totals">

        {{-- Starpsumma --}}
        <div class="totals-row">
            <div class="totals-label">{{ $isLv ? 'Starpsumma' : 'Subtotal' }}:</div>
            <div class="totals-value">{{ number_format($order->subtotal, 2) }} EUR</div>
        </div>

        {{-- t.sk. PVN — informatīva rinda (PVN jau ir iekļauts cenās) --}}
        <div class="totals-row vat-included-row">
            <div class="totals-label">
                {{ $isLv ? "t.sk. PVN ({$vatRate}%)" : "incl. VAT ({$vatRate}%)" }}:
            </div>
            <div class="totals-value">{{ number_format($vatAmount, 2) }} EUR</div>
        </div>

        {{-- Piegāde --}}
        <div class="totals-row{{ $order->shipping_cost == 0 ? ' shipping-free' : '' }}">
            <div class="totals-label">{{ $isLv ? 'Piegāde' : 'Shipping' }}:</div>
            <div class="totals-value">
                @if($order->shipping_cost > 0)
                    {{ number_format($order->shipping_cost, 2) }} EUR
                @else
                    {{ $isLv ? 'Bezmaksas' : 'Free' }}
                @endif
            </div>
        </div>

        {{-- Atlaide (ja ir kupons) --}}
        @if($order->discount_amount > 0)
            <div class="totals-row discount-row">
                <div class="totals-label">
                    {{ $isLv ? 'Atlaide' : 'Discount' }}
                    @if($order->coupon_code)
                        <span class="coupon-badge">{{ $order->coupon_code }}</span>
                    @endif:
                </div>
                <div class="totals-value discount-value">
                    -{{ number_format($order->discount_amount, 2) }} EUR
                </div>
            </div>
        @endif

        {{-- KOPĀ --}}
        <div class="totals-row total-final">
            <div class="totals-label">{{ $isLv ? 'KOPĀ APMAKSAI' : 'TOTAL DUE' }}:</div>
            <div class="totals-value">{{ number_format($order->total_amount, 2) }} EUR</div>
        </div>

    </div>

    {{-- ── MAKSĀJUMA INFO ──────────────────────────────────────── --}}
    @if($order->payment)
        <div class="payment-info">
            <div style="font-size: 10pt; line-height: 1.8;">
                <strong>{{ $isLv ? 'Maksājuma veids' : 'Payment Method' }}:</strong>
                <span class="payment-method">{{ $paymentLabel }}</span>
                @if($order->payment->payment_method === 'card' && $order->payment->card_last4)
                    (•••• {{ $order->payment->card_last4 }}
                    @if($order->payment->card_brand) — {{ $order->payment->card_brand }}@endif)
                @endif
                <br>
                <strong>{{ $isLv ? 'Maksājuma statuss' : 'Payment Status' }}:</strong>
                <span class="status-badge status-{{ $order->payment->status }}">{{ $paymentStatusLabel }}</span>
                @if($order->paid_at)
                    <br>
                    <strong>{{ $isLv ? 'Apmaksāts' : 'Paid' }}:</strong>
                    {{ $order->paid_at->format('d.m.Y H:i') }}
                @endif
            </div>
        </div>
    @endif

    {{-- ── PIEZĪMES ────────────────────────────────────────────── --}}
    @if($order->notes)
        <div class="section">
            <div class="section-title">{{ $isLv ? 'Piezīmes' : 'Notes' }}</div>
            <div style="background: #fef2f2; padding: 15px; border: 1px solid #fecaca; border-radius: 4px;">
                {{ $order->notes }}
            </div>
        </div>
    @endif

    {{-- ── KĀJENE ──────────────────────────────────────────────── --}}
    <div class="footer">
        <p>
            <strong style="color: #dc2626;">
                {{ $isLv
                    ? "Paldies par pirkumu {$company['name']} veikalā!"
                    : "Thank you for shopping at {$company['name']}!" }}
            </strong><br>
            {{ $isLv ? 'Jautājumu gadījumā sazinieties ar mums' : 'For any questions, contact us' }}:
            {{ $company['email'] }} | {{ $company['phone'] }}<br>
            <strong>{{ $company['website'] }}</strong>
        </p>
    </div>

</div>
</body>
</html>
