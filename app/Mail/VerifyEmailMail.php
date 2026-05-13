<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use App\Helpers\LocaleHelper;
use Illuminate\Queue\SerializesModels;

class VerifyEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $verificationUrl;
    public string $userName;
    public string $mailLocale;

    /**
     * Izveido jaunu ziņojuma piemēru
     */
    public function __construct(string $verificationUrl, string $userName, string $mailLocale = 'lv')
    {
        $this->verificationUrl = $verificationUrl;
        $this->userName = $userName;
        $this->mailLocale = $mailLocale;
    }

    /**
     * Iegūst ziņojuma aploksni
     */
    public function envelope(): Envelope
    {
        LocaleHelper::set($this->mailLocale);

        return new Envelope(
            subject: __('email.verify.subject') . ' - RalphMania',
        );
    }

    /**
     * Iegūst ziņojuma satura definīciju
     */
    public function content(): Content
    {
        LocaleHelper::set($this->mailLocale);

        return new Content(
            view: 'emails.verify-email',
            with: [
                'verificationUrl' => $this->verificationUrl,
                'userName' => $this->userName,
            ],
        );
    }
}
