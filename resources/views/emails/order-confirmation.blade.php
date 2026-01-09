<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Order $order
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pasūtījuma apstiprinājums - ' . $this->order->order_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
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
        $data = [
            'order' => $this->order,
            'company' => [
                'name' => 'RalphMania',
                'address' => 'Brīvības iela 1',
                'city' => 'Rīga, LV-1010',
                'country' => 'Latvia',
                'phone' => '+371 20000000',
                'email' => 'info@ralphmania.com',
                'website' => 'www.ralphmania.com',
                'reg_number' => '40103123456',
                'vat_number' => 'LV40103123456',
            ],
        ];

        return Pdf::loadView('invoices.order', $data);
    }
}<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Order $order
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pasūtījuma apstiprinājums - ' . $this->order->order_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
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
        $data = [
            'order' => $this->order,
            'company' => [
                'name' => 'RalphMania',
                'address' => 'Brīvības iela 1',
                'city' => 'Rīga, LV-1010',
                'country' => 'Latvia',
                'phone' => '+371 20000000',
                'email' => 'info@ralphmania.com',
                'website' => 'www.ralphmania.com',
                'reg_number' => '40103123456',
                'vat_number' => 'LV40103123456',
            ],
        ];

        return Pdf::loadView('invoices.order', $data);
    }
}
