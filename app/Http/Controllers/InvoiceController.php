<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\LocaleHelper;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Ģenerē un lejupielādē rēķinu PDF formātā
     *
     * GET /orders/{id}/invoice
     */
    public function download($orderId)
    {
        // Get order with relations
        $order = Order::with(['items.product', 'payment', 'user'])
            ->where('id', $orderId)
            ->where('user_id', auth()->id()) // Drošība: tikai pašu pasūtījumi
            ->firstOrFail();

        // Sagatavo datus PDF failam
        $data = [
            'order' => $order,
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

        // Iestata valodu pēc lietotāja preferences
        LocaleHelper::setForUser($order->user);

        // Ģenerē PDF
        $pdf = Pdf::loadView('invoices.order', $data);

        // Lejupielādē ar faila nosaukumu: Invoice-RM-20260106-ABC123.pdf
        return $pdf->download('Invoice-' . $order->order_number . '.pdf');
    }

    /**
     * Skata rēķinu pārlūkprogrammā (pēc izvēles)
     *
     * GET /orders/{id}/invoice/view
     */
    public function view($orderId)
    {
        $order = Order::with(['items.product', 'payment', 'user'])
            ->where('id', $orderId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $data = [
            'order' => $order,
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

        LocaleHelper::setForUser($order->user);

        $pdf = Pdf::loadView('invoices.order', $data);

        // Parādās pārlūkprogrammā
        return $pdf->stream('Invoice-' . $order->order_number . '.pdf');
    }
}
