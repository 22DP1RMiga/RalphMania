<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use App\Helpers\LocaleHelper;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Order $order,
        public string $locale = 'lv',
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        LocaleHelper::set($this->locale);

        return new Envelope(
            subject: __('email.order.subject') . ' - ' . $this->order->order_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        LocaleHelper::set($this->locale);

        return new Content(
            view: 'emails.order-confirmation',
            with: [
                'order' => $this->order,
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
        // Generate PDF invoice and attach it
        $pdf = $this->generateInvoicePdf();

        return [
            Attachment::fromData(fn () => $pdf->output(), 'Invoice-' . $this->order->order_number . '.pdf')
                ->withMime('application/pdf'),
        ];
    }

    /**
     * Generate invoice PDF
     */
    private function generateInvoicePdf()
    {
        $this->order->loadMissing(['items.product', 'payment']);

        $data = [
            'order' => $this->order,
            'company' => [
                'name' => 'RalphMania',
                'address' => 'Brīvības iela 1',
                'city' => 'Rīga, LV-1010',
                'country' => 'Latvia',
                'phone' => '+371 20000000',
                'email' => 'ralphmania.roltonslv@gmail.com',
                'website' => 'https://ralphmania.rvtdev.tech/',
                'reg_number' => '40103123456',
                'vat_number' => 'LV40103123456',
            ],
        ];

        return Pdf::loadView('invoices.order', $data);
    }
}
