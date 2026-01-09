<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jauns kontakta ziņojums - RalphMania</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        .header p {
            margin: 10px 0 0;
            opacity: 0.9;
            font-size: 14px;
        }
        .content {
            padding: 30px;
        }
        .info-group {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .info-group:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        .info-label {
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 16px;
            color: #111827;
        }
        .info-value a {
            color: #dc2626;
            text-decoration: none;
        }
        .info-value a:hover {
            text-decoration: underline;
        }
        .message-box {
            background-color: #f9fafb;
            border-radius: 8px;
            padding: 20px;
            margin-top: 10px;
        }
        .message-text {
            white-space: pre-wrap;
            color: #374151;
        }
        .footer {
            background-color: #f9fafb;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }
        .badge {
            display: inline-block;
            background-color: #dc2626;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
        }
        .user-badge {
            background-color: #10b981;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>📧 Jauns kontakta ziņojums</h1>
        <p>RalphMania mājaslapa</p>
    </div>

    <div class="content">
        <!-- Sūtītāja informācija -->
        <div class="info-group">
            <div class="info-label">Sūtītājs</div>
            <div class="info-value">
                {{ $contactMessage->name }}
                @if($contactMessage->user_id)
                    <span class="badge user-badge">Reģistrēts lietotājs</span>
                @endif
            </div>
        </div>

        <!-- E-pasts -->
        <div class="info-group">
            <div class="info-label">E-pasts</div>
            <div class="info-value">
                <a href="mailto:{{ $contactMessage->email }}">{{ $contactMessage->email }}</a>
            </div>
        </div>

        <!-- Tālrunis (ja norādīts) -->
        @if($contactMessage->phone)
            <div class="info-group">
                <div class="info-label">Tālrunis</div>
                <div class="info-value">
                    <a href="tel:{{ $contactMessage->country_code }}{{ $contactMessage->phone }}">
                        {{ $contactMessage->country_code }} {{ $contactMessage->phone }}
                    </a>
                </div>
            </div>
        @endif

        <!-- Tēma -->
        <div class="info-group">
            <div class="info-label">Tēma</div>
            <div class="info-value">{{ $contactMessage->subject }}</div>
        </div>

        <!-- Ziņojums -->
        <div class="info-group">
            <div class="info-label">Ziņojums</div>
            <div class="message-box">
                <div class="message-text">{{ $contactMessage->message }}</div>
            </div>
        </div>

        <!-- Laiks -->
        <div class="info-group">
            <div class="info-label">Nosūtīts</div>
            <div class="info-value">{{ $contactMessage->created_at->format('d.m.Y H:i') }}</div>
        </div>
    </div>

    <div class="footer">
        <p>Šis e-pasts tika automātiski nosūtīts no RalphMania kontakta formas.</p>
        <p>© {{ date('Y') }} RalphMania. Visas tiesības aizsargātas.</p>
    </div>
</div>
</body>
</html>
