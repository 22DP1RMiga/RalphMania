<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display user's orders
     */
    public function index(Request $request)
    {
        $orders = Order::with('items.product')
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($orders);
    }

    /**
     * Display single order
     */
    public function show(Request $request, $id)
    {
        $order = Order::with(['items.product', 'payment'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json($order);
    }

    /**
     * Create new order from cart
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:100',
            'customer_email' => 'required|email|max:100',
            'customer_phone' => 'required|string|max:20',
            'delivery_country' => 'required|string|max:50',
            'delivery_city' => 'required|string|max:50',
            'delivery_address' => 'required|string|max:100',
            'delivery_postal_code' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'payment_method' => 'required|in:card,paypal,bank_transfer',
        ]);

        DB::beginTransaction();

        try {
            $user = $request->user();

            // Get cart items
            $cartItems = CartItem::with('product')
                ->where('user_id', $user->id)
                ->get();

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'message' => 'Grozs ir tukšs'
                ], 422);
            }

            // Calculate totals
            $subtotal = $cartItems->sum(function ($item) {
                return $item->quantity * $item->price;
            });

            $shippingCost = $subtotal > 50 ? 0 : 5;
            $totalAmount = $subtotal + $shippingCost;

            // Create order
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $user->id,
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'delivery_country' => $validated['delivery_country'],
                'delivery_city' => $validated['delivery_city'],
                'delivery_address' => $validated['delivery_address'],
                'delivery_postal_code' => $validated['delivery_postal_code'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total_amount' => $totalAmount,
                'status' => 'pending',
            ]);

            // Create order items
            foreach ($cartItems as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'product_name' => $cartItem->product->name_lv,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                ]);

                // Decrease stock
                $cartItem->product->decrement('stock_quantity', $cartItem->quantity);
            }

            // Create payment record
            Payment::create([
                'order_id' => $order->id,
                'payment_method' => $validated['payment_method'],
                'amount' => $totalAmount,
                'currency' => 'EUR',
                'status' => 'pending',
            ]);

            // Clear cart
            CartItem::where('user_id', $user->id)->delete();

            DB::commit();

            return response()->json([
                'message' => 'Pasūtījums veiksmīgi izveidots',
                'order' => $order->load(['items.product', 'payment']),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Kļūda veidojot pasūtījumu',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel order
     */
    public function cancel(Request $request, $id)
    {
        $order = Order::where('user_id', $request->user()->id)
            ->findOrFail($id);

        if (!$order->canBeCancelled()) {
            return response()->json([
                'message' => 'Šo pasūtījumu vairs nevar atcelt'
            ], 422);
        }

        // Return stock
        foreach ($order->items as $item) {
            $item->product->increment('stock_quantity', $item->quantity);
        }

        $order->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Pasūtījums atcelts',
            'order' => $order
        ]);
    }

    /**
     * Admin: Get all orders
     */
    public function adminIndex(Request $request)
    {
        $query = Order::with(['user', 'items.product']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by order number
        if ($request->has('search')) {
            $query->where('order_number', 'LIKE', "%{$request->search}%")
                ->orWhere('customer_name', 'LIKE', "%{$request->search}%");
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($orders);
    }

    /**
     * Admin: Update order status
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,processing,packed,shipped,in_transit,delivered,cancelled,refunded',
            'tracking_number' => 'nullable|string|max:100',
        ]);

        $order = Order::findOrFail($id);
        $order->update($validated);

        // Update timestamps based on status
        if ($validated['status'] === 'shipped') {
            $order->update(['shipped_at' => now()]);
        } elseif ($validated['status'] === 'delivered') {
            $order->update(['delivered_at' => now()]);
        }

        return response()->json([
            'message' => 'Pasūtījuma statuss atjaunināts',
            'order' => $order
        ]);
    }
}
