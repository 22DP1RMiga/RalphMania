<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use App\Helpers\LocaleHelper;
use Illuminate\Queue\SerializesModels;

class ContactReply extends Mailable
{
    use Queueable, SerializesModels;

    public ContactMessage $contactMessage;
    public string $replyText;
    public string $mailLocale;

    /**
     * Izveido jaunu ziņojuma piemēru
     */
    public function __construct(ContactMessage $contactMessage, string $replyText, string $mailLocale = 'lv')
    {
        $this->contactMessage = $contactMessage;
        $this->replyText = $replyText;
        $this->mailLocale = $mailLocale;
    }

    /**
     * Iegūst ziņojuma aploksni
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Re: ' . $this->contactMessage->subject,
            replyTo: [config('mail.from.address', 'noreply@ralphmania.com')],
        );
    }

    /**
     * Iegūst ziņojuma satura definīciju
     */
    public function content(): Content
    {
        LocaleHelper::set($this->mailLocale);

        return new Content(
            view: 'emails.contact-reply',
            with: [
                'contactMessage' => $this->contactMessage,
                'replyText' => $this->replyText,
            ],
        );
    }

    /**
     * Iegūst ziņojuma pielikumus
     */
    public function attachments(): array
    {
        return [];
    }
}
