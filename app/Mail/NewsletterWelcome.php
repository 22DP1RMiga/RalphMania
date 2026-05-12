<?php

namespace App\Mail;

use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use App\Helpers\LocaleHelper;
use Illuminate\Queue\SerializesModels;

class NewsletterWelcome extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public NewsletterSubscriber $subscriber,
        public string $userName = '',
        public string $mailLocale = 'lv',
    ) {}

    public function envelope(): Envelope
    {
        LocaleHelper::set($this->mailLocale);

        return new Envelope(
            subject: __('email.newsletter.subject'),
        );
    }

    public function content(): Content
    {
        LocaleHelper::set($this->mailLocale);

        return new Content(
            view: 'emails.newsletter-welcome',
            with: [
                'subscriber'        => $this->subscriber,
                'userName'          => $this->userName,
                'unsubscribeUrl'    => route('newsletter.unsubscribe', $this->subscriber->token),
                'shopUrl'           => config('app.url') . '/shop',
                'expiresAt'         => $this->subscriber->subscription_expires_at,
            ],
        );
    }
}
