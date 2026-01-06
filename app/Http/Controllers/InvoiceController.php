<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Generate and download invoice PDF
     *
     * GET /orders/{id}/invoice
     */
    public function download($orderId)
    {
        // Get order with relations
        $order = Order::with(['items.product', 'payment', 'user'])
            ->where('id', $orderId)
            ->where('user_id', auth()->id()) // Security: only own orders
            ->firstOrFail();

        // Prepare data for PDF
        $data = [
            'order' => $order,
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

        // Generate PDF
        $pdf = Pdf::loadView('invoices.order', $data);

        // Download with filename: Invoice-RM-20260106-ABC123.pdf
        return $pdf->download('Invoice-' . $order->order_number . '.pdf');
    }

    /**
     * View invoice in browser (optional)
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
                'email' => 'info@ralphmania.com',
                'website' => 'www.ralphmania.com',
                'reg_number' => '40103123456',
                'vat_number' => 'LV40103123456',
            ],
        ];

        $pdf = Pdf::loadView('invoices.order', $data);

        // Display in browser
        return $pdf->stream('Invoice-' . $order->order_number . '.pdf');
    }
}
