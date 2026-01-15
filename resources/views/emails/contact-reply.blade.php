<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Re: {{ $contactMessage->subject }}</title>
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
            padding: 24px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        .email-header p {
            margin: 8px 0 0;
            opacity: 0.9;
            font-size: 14px;
        }
        .email-body {
            padding: 32px 24px;
        }
        .greeting {
            font-size: 18px;
            color: #111827;
            margin-bottom: 20px;
        }
        .reply-content {
            background-color: #f9fafb;
            border-left: 4px solid #dc2626;
            padding: 20px;
            margin: 24px 0;
            border-radius: 0 8px 8px 0;
        }
        .reply-content p {
            margin: 0;
            white-space: pre-wrap;
            color: #374151;
        }
        .original-message {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
        }
        .original-label {
            font-size: 12px;
            text-transform: uppercase;
            color: #6b7280;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }
        .original-content {
            background-color: #f3f4f6;
            padding: 16px;
            border-radius: 8px;
            font-size: 14px;
            color: #4b5563;
        }
        .original-content .subject {
            font-weight: 600;
            color: #111827;
            margin-bottom: 8px;
        }
        .email-footer {
            background-color: #f9fafb;
            padding: 24px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .email-footer p {
            margin: 0;
            font-size: 14px;
            color: #6b7280;
        }
        .email-footer a {
            color: #dc2626;
            text-decoration: none;
        }
        .email-footer a:hover {
            text-decoration: underline;
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
    <div class="email-header">
        <h1>RalphMania</h1>
        <p>Atbilde uz jūsu ziņojumu</p>
    </div>

    <div class="email-body">
        <p class="greeting">Sveiki, {{ $contactMessage->name }}!</p>

        <p>Paldies, ka sazinājāties ar mums. Šeit ir mūsu atbilde uz jūsu jautājumu:</p>

        <div class="reply-content">
            <p>{{ $replyText }}</p>
        </div>

        <div class="original-message">
            <p class="original-label">Jūsu oriģinālais ziņojums:</p>
            <div class="original-content">
                <p class="subject">{{ $contactMessage->subject }}</p>
                <p>{{ $contactMessage->message }}</p>
            </div>
        </div>

        <div class="signature">
            <p>Ar cieņu,<br><strong>RalphMania komanda</strong></p>
        </div>
    </div>

    <div class="email-footer">
        <p>
            Ja jums ir vēl kādi jautājumi, droši rakstiet mums atpakaļ!<br>
            <a href="{{ config('app.url') }}">{{ config('app.url') }}</a>
        </p>
    </div>
</div>
</body>
</html>
