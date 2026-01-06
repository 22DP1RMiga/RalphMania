<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmation;

class OrderController extends Controller
{
    /**
     * Display user's order history
     *
     * GET /orders
     */
    public function index(): Response
    {
        $orders = Order::where('user_id', auth()->id())
            ->with(['items.product', 'payment'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
        ]);
    }

    /**
     * Display single order details
     *
     * GET /orders/{id}
     */
    public function show($id): Response
    {
        $order = Order::with(['items.product', 'payment'])
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return Inertia::render('Orders/Show', [
            'order' => $order,
        ]);
    }

    /**
     * Create new order from cart
     *
     * POST /orders
     *
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        // Base validation rules
        $rules = [
            'customer_name' => 'required|string|max:100',
            'customer_email' => 'required|email|max:100',
            'customer_phone' => 'required|string|max:20',
            'delivery_country' => 'required|string|max:50',
            'delivery_city' => 'required|string|max:50',
            'delivery_address' => 'required|string|max:100',
            'delivery_postal_code' => 'nullable|string|max:20',
            'payment_method' => 'required|in:card,bank_transfer,cash_on_delivery',
            'notes' => 'nullable|string|max:500',
        ];

        // Add card validation ONLY if payment_method is card
        if ($request->payment_method === 'card') {
            $rules['card_number'] = 'required|string|min:13|max:19';
            $rules['card_name'] = 'required|string|max:100';
            $rules['card_expiry'] = ['required', 'string', 'regex:/^(0[1-9]|1[0-2])\/\d{2}$/'];
            $rules['card_cvv'] = 'required|string|min:3|max:4';
        }

        $validated = $request->validate($rules);

        $user = auth()->user();

        // Get user's cart
        $cart = Cart::where('user_id', $user->id)
            ->with('items.product')
            ->first();

        // DEBUG: Check cart
        \Log::info('Cart check:', [
            'cart_exists' => $cart ? 'yes' : 'no',
            'items_count' => $cart ? $cart->items->count() : 0,
            'cart_items' => $cart ? $cart->items->toArray() : [],
        ]);

        if (!$cart || $cart->items->isEmpty()) {
            \Log::warning('Cart is empty for user: ' . $user->id);
            return back()->with('error', 'Cart is empty');
        }

        // Calculate totals from cart items (since cart->total_amount might be NULL)
        $subtotal = 0;
        foreach ($cart->items as $item) {
            $subtotal += $item->product->price * $item->quantity;
        }

        $shippingCost = $this->calculateShipping($validated['delivery_country'], $subtotal);
        $totalAmount = $subtotal + $shippingCost;

        // DEBUG: Log totals
        \Log::info('Order totals:', [
            'subtotal' => $subtotal,
            'shipping' => $shippingCost,
            'total' => $totalAmount,
        ]);

        DB::beginTransaction();

        try {
            // Create order
            $orderData = [
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $user->id,
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'delivery_country' => $validated['delivery_country'],
                'delivery_city' => $validated['delivery_city'],
                'delivery_address' => $validated['delivery_address'],
                'delivery_postal_code' => $validated['delivery_postal_code'] ?? null,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ];

            // DEBUG: Log order data
            \Log::info('Creating order with data:', $orderData);

            $order = Order::create($orderData);

            // DEBUG: Check order created
            \Log::info('Order created:', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]);

            // Create order items
            // ✅ FIXED: Match database schema (product_name required, no subtotal)
            foreach ($cart->items as $item) {
                $itemData = [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name_lv,  // ✅ ADDED (required)
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                    // Note: 'total' is auto-calculated in DB as (quantity * price)
                ];

                \Log::info('Creating order item:', $itemData);

                $order->items()->create($itemData);
            }

            // Process card data if payment method is card
            $cardData = [];
            if ($validated['payment_method'] === 'card' && isset($validated['card_number'])) {
                // Extract card details
                $cardNumber = str_replace(' ', '', $validated['card_number']);
                $last4 = substr($cardNumber, -4);
                $brand = $this->detectCardBrand($cardNumber);

                // Parse expiry (MM/YY)
                [$month, $year] = explode('/', $validated['card_expiry']);
                $fullYear = '20' . $year; // Convert YY to YYYY

                $cardData = [
                    'card_last4' => $last4,
                    'card_brand' => $brand,
                    'card_exp_month' => $month,
                    'card_exp_year' => $fullYear,
                ];

                \Log::info('Card data:', $cardData);
            }

            // Create payment record
            $paymentData = array_merge([
                'order_id' => $order->id,
                'payment_method' => $validated['payment_method'],
                'amount' => $totalAmount,
                'currency' => 'EUR',
                'status' => 'pending',
            ], $cardData);

            \Log::info('Creating payment with data:', $paymentData);

            $payment = Payment::create($paymentData);

            \Log::info('Payment created:', [
                'payment_id' => $payment->id,
            ]);

            // Clear cart
            $deletedItems = $cart->items()->delete();
            \Log::info('Deleted cart items:', ['count' => $deletedItems]);

            DB::commit();

            \Log::info('Order creation SUCCESS!', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]);

            // ✅ SEND ORDER CONFIRMATION EMAIL
            try {
                \Log::info('Sending order confirmation email', [
                    'order_id' => $order->id,
                    'email' => $order->customer_email,
                ]);

                Mail::to($order->customer_email)
                    ->send(new OrderConfirmation($order));

                \Log::info('Order confirmation email sent successfully');
            } catch (\Exception $e) {
                // Log error but don't fail the order
                \Log::error('Failed to send order confirmation email:', [
                    'error' => $e->getMessage(),
                    'order_id' => $order->id,
                ]);
            }

            return redirect()->route('orders.show', $order->id)
                ->with('success', 'Order placed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Order creation FAILED:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to create order: ' . $e->getMessage());
        }
    }

    /**
     * Generate unique order number
     */
    private function generateOrderNumber(): string
    {
        $prefix = 'RM-';
        $date = date('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));

        return $prefix . $date . '-' . $random;
    }

    /**
     * Calculate shipping cost
     */
    private function calculateShipping(string $country, float $subtotal): float
    {
        // Free shipping over 50€
        if ($subtotal >= 50) {
            return 0;
        }

        $rates = [
            'Latvia' => 3.99,
            'Estonia' => 5.99,
            'Lithuania' => 5.99,
        ];

        return $rates[$country] ?? 5.99;
    }

    /**
     * Detect card brand from card number
     */
    private function detectCardBrand(string $cardNumber): string
    {
        $cardNumber = str_replace(' ', '', $cardNumber);
        $firstDigit = substr($cardNumber, 0, 1);
        $firstTwo = substr($cardNumber, 0, 2);

        // Visa: starts with 4
        if ($firstDigit === '4') {
            return 'Visa';
        }

        // Mastercard: 51-55
        if (in_array($firstTwo, ['51', '52', '53', '54', '55'])) {
            return 'Mastercard';
        }

        // American Express: 34, 37
        if (in_array($firstTwo, ['34', '37'])) {
            return 'American Express';
        }

        // Discover: 60, 64, 65
        if (in_array($firstTwo, ['60', '64', '65'])) {
            return 'Discover';
        }

        return 'Unknown';
    }

    /**
     * Cancel order
     *
     * PUT /orders/{id}/cancel
     */
    public function cancel($id): JsonResponse
    {
        $order = Order::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Can only cancel if not shipped
        if (in_array($order->status, ['shipped', 'in_transit', 'delivered'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel order that is already shipped'
            ], 400);
        }

        DB::beginTransaction();

        try {
            // Restore stock for all items
            foreach ($order->items as $item) {
                $item->product->increment('stock_quantity', $item->quantity);
            }

            // Update order status
            $order->update(['status' => 'cancelled']);

            // Update payment status
            if ($order->payment) {
                $order->payment->update(['status' => 'refunded']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error cancelling order'
            ], 500);
        }
    }

    /**
     * Admin: List all orders
     *
     * GET /admin/orders
     */
    public function adminIndex(): Response
    {
        $orders = Order::with(['user', 'items.product', 'payment'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
        ]);
    }

    /**
     * Admin: Show order details
     *
     * GET /admin/orders/{id}
     */
    public function adminShow($id): Response
    {
        $order = Order::with(['user', 'items.product', 'payment', 'courierAssignments.courier'])
            ->findOrFail($id);

        return Inertia::render('Admin/Orders/Show', [
            'order' => $order,
        ]);
    }

    /**
     * Admin: Update order status
     *
     * PUT /admin/orders/{id}/status
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,processing,packed,shipped,in_transit,delivered,cancelled,refunded',
        ]);

        $order = Order::findOrFail($id);

        $order->update([
            'status' => $validated['status'],
            'shipped_at' => $validated['status'] === 'shipped' ? now() : $order->shipped_at,
            'delivered_at' => $validated['status'] === 'delivered' ? now() : $order->delivered_at,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order status updated',
            'order' => $order,
        ]);
    }
}
