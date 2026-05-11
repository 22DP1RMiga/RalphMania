<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('email.verify.subject') }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #1f2937; background: #f9fafb; margin: 0; padding: 0; }
        .email-wrapper { max-width: 600px; margin: 40px auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); color: white; padding: 40px 30px; text-align: center; }
        .logo { font-size: 32px; font-weight: bold; margin-bottom: 10px; }
        .tagline { font-size: 14px; opacity: 0.9; }
        .content { padding: 40px 30px; }
        .greeting { font-size: 20px; font-weight: bold; color: #1f2937; margin-bottom: 20px; }
        .message { margin-bottom: 15px; color: #4b5563; font-size: 15px; }
        .button-wrapper { text-align: center; margin: 35px 0; }
        .button { display: inline-block; background: #dc2626; color: white; padding: 14px 32px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; }
        .welcome-box { background: #fef2f2; border: 2px solid #fecaca; border-radius: 8px; padding: 20px; margin: 25px 0; text-align: center; }
        .welcome-box h3 { color: #dc2626; margin: 0 0 10px 0; }
        .welcome-box p { margin: 0; color: #4b5563; }
        .subcopy { background: #f9fafb; border-left: 4px solid #dc2626; padding: 15px 20px; margin: 30px 0; font-size: 13px; color: #6b7280; }
        .footer { background: #f9fafb; padding: 30px; text-align: center; border-top: 1px solid #e5e7eb; }
        .footer p { margin: 5px 0; font-size: 13px; color: #6b7280; }
        .footer a { color: #dc2626; text-decoration: none; }
        .salutation { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 14px; }
    </style>
</head>
<body>
<div class="email-wrapper">
    <div class="header">
        <div class="logo">🎉 {{ __('email.verify.title') }}</div>
        <div class="tagline">{{ __('email.tagline') }}</div>
    </div>
    <div class="content">
        <div class="greeting">{{ __('email.greeting') }}, {{ $userName }}!</div>
        <div class="welcome-box">
            <h3>🎊 {{ __('email.verify.welcome_box_title') }}</h3>
            <p>{{ __('email.verify.welcome_box_text') }}</p>
        </div>
        <div class="message">{{ __('email.verify.body') }}</div>
        <div class="button-wrapper">
            <a href="{{ $verificationUrl }}" class="button">{{ __('email.verify.button') }}</a>
        </div>
        <div class="message">{{ __('email.verify.after_title') }}</div>
        <div class="message" style="padding-left: 20px;">
            ✅ {{ __('email.verify.benefit_1') }}<br>
            ✅ {{ __('email.verify.benefit_2') }}<br>
            ✅ {{ __('email.verify.benefit_3') }}<br>
            ✅ {{ __('email.verify.benefit_4') }}
        </div>
        <div class="message">{{ __('email.verify.no_account') }}</div>
        <div class="subcopy">
            <strong>{{ __('email.verify.link_trouble') }}</strong><br><br>
            <a href="{{ $verificationUrl }}" style="color: #dc2626; word-break: break-all;">{{ $verificationUrl }}</a>
        </div>
        <div class="salutation">
            {{ __('email.regards') }}<br>
            <strong>{{ __('email.team') }}</strong>
        </div>
    </div>
    <div class="footer">
        <p><strong>RalphMania</strong></p>
        <p>Brīvības iela 1, Rīga, LV-1010, Latvia</p>
        <p>
            <a href="mailto:ralphmania.roltonslv@gmail.com">ralphmania.roltonslv@gmail.com</a> |
            <a href="{{ config('app.url') }}">{{ config('app.url') }}</a>
        </p>
        <p style="margin-top: 20px; font-size: 12px;">© {{ date('Y') }} RalphMania. {{ __('email.footer_rights') }}</p>
    </div>
</div>
</body>
</html>
