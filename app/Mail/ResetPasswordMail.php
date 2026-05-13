<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use App\Helpers\LocaleHelper;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $resetUrl;
    public string $userName;
    public string $mailLocale;

    /**
     * Create a new message instance.
     */
    public function __construct(string $resetUrl, string $userName, string $mailLocale = 'lv')
    {
        $this->resetUrl = $resetUrl;
        $this->userName = $userName;
        $this->mailLocale = $mailLocale;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        LocaleHelper::set($this->mailLocale);

        return new Envelope(
            subject: __('email.reset.subject') . ' - RalphMania',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        LocaleHelper::set($this->mailLocale);

        return new Content(
            view: 'emails.reset-password',
            with: [
                'resetUrl' => $this->resetUrl,
                'userName' => $this->userName,
            ],
        );
    }
}
