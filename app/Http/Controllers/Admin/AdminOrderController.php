<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminOrderController extends Controller
{
    private function getCompanyInfo()
    {
        return [
            'name' => config('app.company_name', 'RalphMania'),
            'address' => 'Brīvības iela 123',
            'city' => 'Rīga, LV-1001',
            'country' => 'Latvija',
            'reg_number' => '40001234567',
            'vat_number' => 'LV40001234567',
            'email' => 'info@ralphmania.lv',
            'phone' => '+371 20000000',
            'website' => 'www.ralphmania.lv',
        ];
    }

    public function index(Request $request)
    {
        $query = Order::withCount('items')
            ->with(['user'])
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->paginate(15)->withQueryString();

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'filters' => $request->only(['search', 'status', 'date_from', 'date_to']),
        ]);
    }

    public function show(Order $order)
    {
        $order->load([
            'items.product',
            'payment',
            'user',
        ]);

        return Inertia::render('Admin/Orders/Show', [
            'order' => $order,
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,processing,packed,shipped,in_transit,delivered,cancelled,refunded',
        ]);

        $order->status = $request->status;

        // Atjaunināt papildus datumus
        if ($request->status === 'delivered') {
            $order->delivered_at = now();
        } elseif ($request->status === 'shipped') {
            $order->shipped_at = now();
        }

        $order->save();

        return back()->with('success', 'Pasūtījuma statuss veiksmīgi atjaunināts!');
    }

    public function downloadInvoicePdf(Order $order)
    {
        $order->load('items.product', 'payment', 'user');

        $pdf = Pdf::loadView('invoices.order', [
            'order' => $order,
            'company' => $this->getCompanyInfo(),
        ]);

        return $pdf->download("rekins-{$order->order_number}.pdf");
    }

    public function printInvoice(Order $order)
    {
        $order->load('items.product', 'payment', 'user');

        return view('invoices.order', [
            'order' => $order,
            'company' => $this->getCompanyInfo(),
        ]);
    }
}
