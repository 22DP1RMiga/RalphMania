<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminOrderController extends Controller
{
    /**
     * Display a listing of orders for admin.
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items']);

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                    ->orWhere('customer_name', 'LIKE', "%{$search}%")
                    ->orWhere('customer_email', 'LIKE', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sort
        $query->orderBy('created_at', 'desc');

        // Paginate
        $orders = $query->paginate(20)->through(function ($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
                'customer_phone' => $order->customer_phone,
                'subtotal' => (float) $order->subtotal,
                'shipping_cost' => (float) $order->shipping_cost,
                'total_amount' => (float) $order->total_amount,
                'status' => $order->status,
                'items_count' => $order->items->count(),
                'created_at' => $order->created_at,
                'user' => $order->user ? [
                    'id' => $order->user->id,
                    'username' => $order->user->username,
                ] : null,
            ];
        });

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'filters' => $request->only(['search', 'status', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Display the specified order.
     */
    public function show($id)
    {
        $order = Order::with(['user', 'items.product', 'payment'])->findOrFail($id);

        return Inertia::render('Admin/Orders/Show', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
                'customer_phone' => $order->customer_phone,
                'delivery_country' => $order->delivery_country,
                'delivery_city' => $order->delivery_city,
                'delivery_address' => $order->delivery_address,
                'delivery_postal_code' => $order->delivery_postal_code,
                'subtotal' => (float) $order->subtotal,
                'shipping_cost' => (float) $order->shipping_cost,
                'total_amount' => (float) $order->total_amount,
                'status' => $order->status,
                'notes' => $order->notes,
                'shipped_at' => $order->shipped_at,
                'delivered_at' => $order->delivered_at,
                'created_at' => $order->created_at,
                'user' => $order->user ? [
                    'id' => $order->user->id,
                    'username' => $order->user->username,
                    'email' => $order->user->email,
                ] : null,
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_name' => $item->product_name,
                        'quantity' => $item->quantity,
                        'price' => (float) $item->price,
                        'total' => (float) ($item->price * $item->quantity),
                        'product' => $item->product ? [
                            'id' => $item->product->id,
                            'slug' => $item->product->slug,
                            'image' => $item->product->image,
                        ] : null,
                    ];
                }),
                'payment' => $order->payment ? [
                    'payment_method' => $order->payment->payment_method,
                    'status' => $order->payment->status,
                    'card_last4' => $order->payment->card_last4,
                    'card_brand' => $order->payment->card_brand,
                ] : null,
            ],
        ]);
    }

    /**
     * Update order status.
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,processing,packed,shipped,in_transit,delivered,cancelled,refunded',
        ]);

        $order = Order::findOrFail($id);

        $updateData = ['status' => $validated['status']];

        // Set timestamps based on status
        if ($validated['status'] === 'shipped' && !$order->shipped_at) {
            $updateData['shipped_at'] = now();
        }
        if ($validated['status'] === 'delivered' && !$order->delivered_at) {
            $updateData['delivered_at'] = now();
        }

        $order->update($updateData);

        return back()->with('success', 'Pasūtījuma statuss veiksmīgi atjaunināts!');
    }
}
