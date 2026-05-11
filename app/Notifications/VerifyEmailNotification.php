<?php

namespace App\Notifications;

use App\Mail\VerifyEmailMail;
use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends BaseVerifyEmail
{
    use Queueable;

    /**
     * Nosūta custom verify-email.blade.php e-pastu,
     * nevis Laravel noklusējuma teksta paziņojumu.
     */
    public function toMail(mixed $notifiable): \Illuminate\Mail\Mailable
    {
        $verificationUrl = $this->verificationUrl($notifiable);
        $userName        = $notifiable->username ?? $notifiable->name ?? $notifiable->email;
        $locale          = $notifiable->locale ?? 'lv';

        return (new VerifyEmailMail($verificationUrl, $userName, $locale))
            ->to($notifiable->email);
    }

    /**
     * Ģenerē parakstītu verifikācijas URL (tāpat kā Laravel iebūvētais).
     */
    protected function verificationUrl(mixed $notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id'   => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
