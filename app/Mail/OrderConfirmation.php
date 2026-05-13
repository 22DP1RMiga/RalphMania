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
     * Izveido jaunu ziņojuma piemēru
     */
    public function __construct(
        public Order $order,
        public string $mailLocale = 'lv',
    ) {}

    /**
     * Iegūst ziņojuma aploksni
     */
    public function envelope(): Envelope
    {
        LocaleHelper::set($this->mailLocale);

        return new Envelope(
            subject: __('email.order.subject') . ' - ' . $this->order->order_number,
        );
    }

    /**
     * Iegūst ziņojuma satura definīciju
     */
    public function content(): Content
    {
        LocaleHelper::set($this->mailLocale);

        return new Content(
            view: 'emails.order-confirmation',
            with: [
                'order' => $this->order,
            ],
        );
    }

    /**
     * Iegūst ziņojuma pielikumus
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        // Ģenerē PDF rēķinu un pievieno to
        $pdf = $this->generateInvoicePdf();

        return [
            Attachment::fromData(fn () => $pdf->output(), 'Invoice-' . $this->order->order_number . '.pdf')
                ->withMime('application/pdf'),
        ];
    }

    /**
     * Ģenerē rēķinu PDF formātā
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
