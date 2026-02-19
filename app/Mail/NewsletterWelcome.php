<?php

namespace App\Mail;

use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterWelcome extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public NewsletterSubscriber $subscriber,
        public string $userName = '',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Laipni lūdzam RalphMania abonentiem! 🎉',
        );
    }

    public function content(): Content
    {
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
