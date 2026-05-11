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
    public string $locale;

    /**
     * Create a new message instance.
     */
    public function __construct(string $verificationUrl, string $userName, string $locale = 'lv')
    {
        $this->verificationUrl = $verificationUrl;
        $this->userName = $userName;
        $this->locale = $locale;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        LocaleHelper::set($this->locale);

        return new Envelope(
            subject: __('email.verify.subject') . ' - RalphMania',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        LocaleHelper::set($this->locale);

        return new Content(
            view: 'emails.verify-email',
            with: [
                'verificationUrl' => $this->verificationUrl,
                'userName' => $this->userName,
            ],
        );
    }
}
