<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
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
     * POST /orders
     */
    public function store(Request $request)
    {
        // ─── VALIDĀCIJA ──────────────────────────────────────────────────────
        $rules = [
            'customer_name'        => 'required|string|max:100',
            'customer_email'       => 'required|email|max:100',
            'customer_phone'       => 'required|string|max:20',
            'delivery_country'     => 'required|string|max:50',
            'delivery_city'        => 'required|string|max:50',
            'delivery_address'     => 'required|string|max:100',
            'delivery_postal_code' => 'nullable|string|max:20',
            'payment_method'       => 'required|in:card,bank_transfer,cash_on_delivery',
            'notes'                => 'nullable|string|max:500',
            'coupon_code'          => 'nullable|string|max:64',  // ← JAUNS
        ];

        if ($request->payment_method === 'card') {
            $rules['card_number'] = 'required|string|min:13|max:19';
            $rules['card_name']   = 'required|string|max:100';
            $rules['card_expiry'] = ['required', 'string', 'regex:/^(0[1-9]|1[0-2])\/\d{2}$/'];
            $rules['card_cvv']    = 'required|string|min:3|max:4';
        }

        $validated = $request->validate($rules);

        $user = auth()->user();

        // ─── GROZA PĀRBAUDE ──────────────────────────────────────────────────
        $cart = Cart::where('user_id', $user->id)
            ->with('items.product')
            ->first();

        \Log::info('Cart check:', [
            'cart_exists'  => $cart ? 'yes' : 'no',
            'items_count'  => $cart ? $cart->items->count() : 0,
        ]);

        if (!$cart || $cart->items->isEmpty()) {
            \Log::warning('Cart is empty for user: ' . $user->id);
            return back()->with('error', 'Cart is empty');
        }

        // ─── SUMMU APRĒĶINS ──────────────────────────────────────────────────
        $subtotal = 0;
        foreach ($cart->items as $item) {
            $subtotal += $item->product->price * $item->quantity;
        }

        $shippingCost = $this->calculateShipping($validated['delivery_country'], $subtotal);

        // ─── KUPONA APSTRĀDE ─────────────────────────────────────────────────
        $discountAmount = 0;
        $coupon         = null;
        $couponCode     = null;

        if (!empty($validated['coupon_code'])) {
            $couponCode = strtoupper(trim($validated['coupon_code']));

            $coupon = Coupon::where('is_active', true)
                ->where('code', $couponCode)
                ->where(function ($q) {
                    $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                })
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                })
                ->first();

            if ($coupon) {
                // Validē servera pusē (drošībai, pat ja frontend jau validēja)
                $validation = $coupon->validate($user->id, $subtotal);

                if ($validation['valid']) {
                    $discountAmount = $validation['discount'];
                    \Log::info('Coupon applied', [
                        'code'     => $couponCode,
                        'discount' => $discountAmount,
                    ]);
                } else {
                    // Kupons nav derīgs — turpina pasūtījumu BEZ atlaides
                    // (nevis atgriežam kļūdu — lietotājs var būt iesūtījis veco kodu)
                    \Log::warning('Coupon validation failed on order store', [
                        'code'    => $couponCode,
                        'reason'  => $validation['message'],
                    ]);
                    $coupon     = null;
                    $couponCode = null;
                }
            } else {
                \Log::warning('Coupon not found: ' . $couponCode);
                $couponCode = null;
            }
        }

        // ─── GALĪGĀ SUMMA ────────────────────────────────────────────────────
        // subtotal + piegāde - atlaide (min 0)
        $totalAmount = max(0, $subtotal + $shippingCost - $discountAmount);

        \Log::info('Order totals:', [
            'subtotal'  => $subtotal,
            'shipping'  => $shippingCost,
            'discount'  => $discountAmount,
            'total'     => $totalAmount,
        ]);

        DB::beginTransaction();

        try {
            // ─── PASŪTĪJUMA IZVEIDE ──────────────────────────────────────────
            $orderData = [
                'order_number'         => $this->generateOrderNumber(),
                'user_id'              => $user->id,
                'customer_name'        => $validated['customer_name'],
                'customer_email'       => $validated['customer_email'],
                'customer_phone'       => $validated['customer_phone'],
                'delivery_country'     => $validated['delivery_country'],
                'delivery_city'        => $validated['delivery_city'],
                'delivery_address'     => $validated['delivery_address'],
                'delivery_postal_code' => $validated['delivery_postal_code'] ?? null,
                'subtotal'             => $subtotal,
                'shipping_cost'        => $shippingCost,
                'discount_amount'      => $discountAmount,   // ← JAUNS
                'coupon_code'          => $couponCode,        // ← JAUNS
                'total_amount'         => $totalAmount,
                'status'               => 'pending',
                'notes'                => $validated['notes'] ?? null,
            ];

            \Log::info('Creating order with data:', $orderData);

            $order = Order::create($orderData);

            \Log::info('Order created:', [
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
            ]);

            // ─── PASŪTĪJUMA RINDAS (AR IZMĒRU) ──────────────────────────────
            foreach ($cart->items as $item) {
                $itemData = [
                    'product_id'   => $item->product_id,
                    'product_name' => mb_substr($item->product->name_lv, 0, 50), // VARCHAR(50) limits
                    'quantity'     => $item->quantity,
                    'price'        => $item->product->price,
                    'size'         => $item->size ?? null,  // ← JAUNS: izmērs no groza
                ];

                \Log::info('Creating order item:', $itemData);
                $order->items()->create($itemData);
            }

            // ─── MAKSĀJUMA IERAKSTS ──────────────────────────────────────────
            $cardData = [];
            if ($validated['payment_method'] === 'card' && isset($validated['card_number'])) {
                $cardNumber = str_replace(' ', '', $validated['card_number']);
                $last4      = substr($cardNumber, -4);
                $brand      = $this->detectCardBrand($cardNumber);
                [$month, $year] = explode('/', $validated['card_expiry']);

                $cardData = [
                    'card_last4'     => $last4,
                    'card_brand'     => $brand,
                    'card_exp_month' => $month,
                    'card_exp_year'  => '20' . $year,
                ];
            }

            $payment = Payment::create(array_merge([
                'order_id'       => $order->id,
                'payment_method' => $validated['payment_method'],
                'amount'         => $totalAmount,
                'currency'       => 'EUR',
                'status'         => 'pending',
            ], $cardData));

            \Log::info('Payment created:', ['payment_id' => $payment->id]);

            // ─── KUPONA ATZĪMĒŠANA KĀ IZLIETOTS ─────────────────────────────
            // Tiek izsaukts PĒC pasūtījuma izveides, lai order_id ir pieejams
            if ($coupon && $discountAmount > 0) {
                $coupon->markUsed($user->id, $order->id, $discountAmount);

                \Log::info('Coupon marked as used', [
                    'coupon_code'    => $coupon->code,
                    'order_id'       => $order->id,
                    'discount'       => $discountAmount,
                    'reusable_after' => now()->addDays($coupon->cooldown_days)->toDateString(),
                ]);
            }

            // ─── GROZA IZTĪRĪŠANA ────────────────────────────────────────────
            $deletedItems = $cart->items()->delete();
            \Log::info('Cart cleared:', ['deleted_items' => $deletedItems]);

            DB::commit();

            \Log::info('Order creation SUCCESS!', [
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
            ]);

            // ─── APSTIPRINĀJUMA E-PASTS ──────────────────────────────────────
            try {
                Mail::to($order->customer_email)->send(new OrderConfirmation($order));
                \Log::info('Order confirmation email sent', ['order_id' => $order->id]);
            } catch (\Exception $e) {
                \Log::error('Failed to send order confirmation email', [
                    'error'    => $e->getMessage(),
                    'order_id' => $order->id,
                ]);
                // E-pasta kļūda neaptur pasūtījumu
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

    // ─── PRIVATE HELPERS ─────────────────────────────────────────────────────

    private function generateOrderNumber(): string
    {
        return 'RM-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));
    }

    private function calculateShipping(string $country, float $subtotal): float
    {
        if ($subtotal >= 50) return 0;

        return match($country) {
            'Latvia'    => 3.99,
            'Estonia'   => 5.99,
            'Lithuania' => 5.99,
            default     => 5.99,
        };
    }

    private function detectCardBrand(string $cardNumber): string
    {
        $cardNumber = str_replace(' ', '', $cardNumber);
        $first1     = substr($cardNumber, 0, 1);
        $first2     = substr($cardNumber, 0, 2);

        if ($first1 === '4') return 'Visa';
        if (in_array($first2, ['51','52','53','54','55'])) return 'Mastercard';
        if (in_array($first2, ['34','37'])) return 'American Express';
        if (in_array($first2, ['60','64','65'])) return 'Discover';

        return 'Unknown';
    }

    // ─── PUBLIC ROUTES ───────────────────────────────────────────────────────

    /**
     * Cancel order
     * PUT /orders/{id}/cancel
     */
    public function cancel($id): JsonResponse
    {
        $order = Order::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if (in_array($order->status, ['shipped', 'in_transit', 'delivered'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel order that is already shipped',
            ], 400);
        }

        DB::beginTransaction();

        try {
            foreach ($order->items as $item) {
                $item->product->increment('stock_quantity', $item->quantity);
            }

            $order->update(['status' => 'cancelled']);

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
                'message' => 'Error cancelling order',
            ], 500);
        }
    }

    // ─── ADMIN ROUTES ────────────────────────────────────────────────────────

    public function adminIndex(): Response
    {
        $orders = Order::with(['user', 'items.product', 'payment'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
        ]);
    }

    public function adminShow($id): Response
    {
        $order = Order::with(['user', 'items.product', 'payment', 'courierAssignments.courier'])
            ->findOrFail($id);

        return Inertia::render('Admin/Orders/Show', [
            'order' => $order,
        ]);
    }

    public function updateStatus(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,processing,packed,shipped,in_transit,delivered,cancelled,refunded',
        ]);

        $order = Order::findOrFail($id);

        $order->update([
            'status'       => $validated['status'],
            'shipped_at'   => $validated['status'] === 'shipped'   ? now() : $order->shipped_at,
            'delivered_at' => $validated['status'] === 'delivered' ? now() : $order->delivered_at,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order status updated',
            'order'   => $order,
        ]);
    }
}
