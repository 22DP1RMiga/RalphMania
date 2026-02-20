<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laipni lūdzam RalphMania abonentiem!</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            padding: 32px 24px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0 0 8px;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .email-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 15px;
        }
        .confetti-emoji {
            font-size: 2.5rem;
            margin-bottom: 12px;
            display: block;
        }
        .email-body {
            padding: 32px 24px;
        }
        .greeting {
            font-size: 18px;
            color: #111827;
            margin-bottom: 12px;
            font-weight: 600;
        }
        .intro-text {
            color: #4b5563;
            margin-bottom: 28px;
            font-size: 15px;
        }
        /* Subscription info box */
        .subscription-box {
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
            border: 1px solid #10b981;
            border-radius: 10px;
            padding: 20px 24px;
            margin: 24px 0;
        }
        .subscription-box-title {
            font-size: 15px;
            font-weight: 700;
            color: #065f46;
            margin: 0 0 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .subscription-detail {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            color: #047857;
            padding: 4px 0;
        }
        .subscription-detail strong {
            color: #065f46;
        }
        /* Benefits section */
        .benefits-title {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
            margin: 28px 0 16px;
        }
        .benefit-item {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .benefit-item:last-child {
            border-bottom: none;
        }
        .benefit-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
            text-align: center;
            line-height: 36px;
        }
        .benefit-content h4 {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
            margin: 0 0 4px;
        }
        .benefit-content p {
            font-size: 13px;
            color: #6b7280;
            margin: 0;
        }
        /* CTA Button */
        .cta-section {
            text-align: center;
            margin: 32px 0;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 15px;
            letter-spacing: 0.3px;
        }
        /* Expiry notice */
        .expiry-notice {
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 14px 18px;
            border-radius: 0 8px 8px 0;
            margin: 24px 0;
            font-size: 13px;
            color: #92400e;
        }
        .expiry-notice strong {
            display: block;
            margin-bottom: 4px;
            font-size: 14px;
        }
        /* Footer */
        .email-footer {
            background-color: #f9fafb;
            padding: 24px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .email-footer p {
            margin: 0 0 8px;
            font-size: 13px;
            color: #6b7280;
        }
        .email-footer a {
            color: #dc2626;
            text-decoration: none;
        }
        .unsubscribe-link {
            font-size: 12px;
            color: #9ca3af !important;
        }
        .signature {
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px dashed #e5e7eb;
            font-size: 14px;
            color: #6b7280;
        }
    </style>
</head>
<body>
<div class="email-container">
    <!-- Header -->
    <div class="email-header">
        <span class="confetti-emoji">🎉</span>
        <h1>RalphMania</h1>
        <p>Tu tagad esi mūsu ekskluzīvo abonēšanas kluba dalībnieks!</p>
    </div>

    <!-- Body -->
    <div class="email-body">
        <p class="greeting">Sveiki, {{ $userName ?: $subscriber->email }}!</p>
        <p class="intro-text">
            Paldies, ka pievienojies RalphMania jaunumu sarakstam. Tu tagad esi viens no mūsu ekskluzīvajiem abonentiem
            un pirmais uzzināsi par jaunumiem, piedāvājumiem un akcijām!
        </p>

        <!-- Subscription Info -->
        <div class="subscription-box">
            <p class="subscription-box-title">✅ Abonēšana apstiprināta</p>
            <div class="subscription-detail">
                <span>E-pasts:</span>
                <strong>{{ $subscriber->email }}</strong>
            </div>
            <div class="subscription-detail">
                <span>Abonēts:</span>
                <strong>{{ now()->format('d.m.Y') }}</strong>
            </div>
            @if($expiresAt)
                <div class="subscription-detail">
                    <span>Derīgs līdz:</span>
                    <strong>{{ \Carbon\Carbon::parse($expiresAt)->format('d.m.Y') }}</strong>
                </div>
            @endif
        </div>

        <!-- Benefits -->
        <p class="benefits-title">Ko tu saņem kā abonents:</p>

        <div class="benefit-item">
            <div class="benefit-icon">🏷️</div>
            <div class="benefit-content">
                <h4>Ekskluzīvas atlaides</h4>
                <p>Piekļuve īpašiem atlaižu kodiem, kuri nav pieejami citiem klientiem.</p>
            </div>
        </div>

        <div class="benefit-item">
            <div class="benefit-icon">🔔</div>
            <div class="benefit-content">
                <h4>Jaunu produktu paziņojumi</h4>
                <p>Pirmais uzzināsi par jaunumiem un kolekkcijām pirms visiem pārējiem.</p>
            </div>
        </div>

        <div class="benefit-item">
            <div class="benefit-icon">🎁</div>
            <div class="benefit-content">
                <h4>Īpašie piedāvājumi</h4>
                <p>Sezonālie piedāvājumi un izpārdošanas tikai abonentiem.</p>
            </div>
        </div>

        <!-- Expiry notice (only if subscription has expiry) -->
        @if($expiresAt)
            <div class="expiry-notice">
                <strong>⏳ Abonēšanas termiņš</strong>
                Tava abonēšana ir derīga līdz <strong>{{ \Carbon\Carbon::parse($expiresAt)->format('d.m.Y') }}</strong>.
                Lai turpinātu saņemt ieguvumus, atjaunini abonēšanu savā profilā.
            </div>
        @endif

        <!-- CTA -->
        <div class="cta-section">
            <a href="{{ $shopUrl }}" class="cta-button">
                🛍️ Doties uz veikalu
            </a>
        </div>

        <div class="signature">
            <p>Ar cieņu,<br><strong>RalphMania komanda</strong></p>
        </div>
    </div>

    <!-- Footer -->
    <div class="email-footer">
        <p>
            Jautājumi? Raksti mums:<br>
            <a href="mailto:ralphmania.roltonslv@gmail.com">ralphmania.roltonslv@gmail.com</a>
        </p>
        <p>
            <a href="{{ $unsubscribeUrl }}" class="unsubscribe-link">
                Atteikties no jaunumiem
            </a>
        </p>
    </div>
</div>
</body>
</html>
