<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use App\Helpers\LocaleHelper;
use Illuminate\Queue\SerializesModels;

class ContactMessageReceived extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public ContactMessage $contactMessage,
        public string $mailLocale = 'lv',
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        LocaleHelper::set($this->mailLocale);

        $prefix = $this->mailLocale === 'en' ? '[RalphMania Contact]' : '[RalphMania Kontakts]';

        return new Envelope(
            from: new Address($this->contactMessage->email, $this->contactMessage->name),
            replyTo: [
                new Address($this->contactMessage->email, $this->contactMessage->name),
            ],
            subject: $prefix . ' ' . $this->contactMessage->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        LocaleHelper::set($this->mailLocale);

        return new Content(
            view: 'emails.contact-message',
            with: [
                'contactMessage' => $this->contactMessage,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
