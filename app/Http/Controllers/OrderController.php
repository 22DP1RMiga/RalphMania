<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\Payment;
use App\Models\SubscriberOffer;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Mail;
use App\Helpers\LocaleHelper;
use App\Mail\OrderConfirmation;

class OrderController extends Controller
{
    /**
     * Rāda lietotāja pasūtījumu vēsturi
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
     * Parāda viena pasūtījuma informāciju
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
     * Izveido jaunu pasūtījumu no groza
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
            'coupon_code'          => 'nullable|string|max:64',
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
        $couponCode     = null;
        $offer          = null;

        if (!empty($validated['coupon_code'])) {
            $code = strtoupper(trim($validated['coupon_code']));

            $offer = SubscriberOffer::active()
                ->where('code', $code)
                ->first();

            if ($offer) {
                // Pārbauda minimālo summu
                $meetsMinimum = !$offer->min_order_amount
                    || $subtotal >= $offer->min_order_amount;

                // Pārbauda abonēšanu (ja kupons tikai abonentiem)
                $meetsSubscription = true;
                if ($offer->subscribers_only) {
                    $meetsSubscription = NewsletterSubscriber::where('user_id', $user->id)
                        ->where('is_active', true)
                        ->where('is_verified', true)
                        ->where(function ($q) {
                            $q->whereNull('subscription_expires_at')
                                ->orWhere('subscription_expires_at', '>=', now());
                        })
                        ->exists();
                }

                if ($meetsMinimum && $meetsSubscription) {
                    // Aprēķina atlaidi
                    if ($offer->discount_type === 'percentage') {
                        $discountAmount = round($subtotal * ($offer->discount_value / 100), 2);
                    } else {
                        $discountAmount = min((float) $offer->discount_value, $subtotal);
                    }
                    $couponCode = $offer->code;

                    \Log::info('Coupon applied at order creation', [
                        'code'     => $couponCode,
                        'discount' => $discountAmount,
                    ]);
                } else {
                    // Kupons neder — turpina bez atlaides
                    \Log::warning('Coupon conditions not met at order creation', [
                        'code'               => $code,
                        'meets_minimum'      => $meetsMinimum,
                        'meets_subscription' => $meetsSubscription,
                    ]);
                    $offer = null;
                }
            } else {
                \Log::warning('Coupon not found at order creation: ' . $code);
            }
        }

        // ─── GALĪGĀ SUMMA ────────────────────────────────────────────────────
        $totalAmount = max(0, $subtotal + $shippingCost - $discountAmount);

        // ─── PVN SADALĪJUMS (t.sk. PVN 21%) ─────────────────────────────────
        // Cenas datubāzē ir BRUTO (ar PVN iekļautu). PVN ir "tai skaitā".
        // PVN bāze = subtotal (produktu summa); piegādei PVN netiek pielietots.
        // vat_amount = subtotal * 21 / 121  (ekvivalents: subtotal - subtotal/1.21)
        $vatRate   = (float) \App\Models\Setting::get('tax_rate', 21);
        $vatAmount = round($subtotal * $vatRate / (100 + $vatRate), 2);
        $subtotalExVat = round($subtotal - $vatAmount, 2);

        \Log::info('Order totals:', [
            'subtotal'         => $subtotal,
            'subtotal_ex_vat'  => $subtotalExVat,
            'vat_amount'       => $vatAmount,
            'vat_rate'         => $vatRate . '%',
            'shipping'         => $shippingCost,
            'discount'         => $discountAmount,
            'total'            => $totalAmount,
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
                'discount_amount'      => $discountAmount,
                'coupon_code'          => $couponCode,
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

            // ─── PASŪTĪJUMA RINDAS ───────────────────────────────────────────
            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_id'   => $item->product_id,
                    'product_name' => mb_substr($item->product->name_lv, 0, 50),
                    'quantity'     => $item->quantity,
                    'price'        => $item->product->price,
                    'size'         => $item->size ?? null,
                ]);
            }

            // ─── MAKSĀJUMA IERAKSTS ──────────────────────────────────────────
            $cardData = [];
            if ($validated['payment_method'] === 'card' && isset($validated['card_number'])) {
                $cardNumber = str_replace(' ', '', $validated['card_number']);
                [$month, $year] = explode('/', $validated['card_expiry']);

                $cardData = [
                    'card_last4'     => substr($cardNumber, -4),
                    'card_brand'     => $this->detectCardBrand($cardNumber),
                    'card_exp_month' => $month,
                    'card_exp_year'  => '20' . $year,
                ];
            }

            Payment::create(array_merge([
                'order_id'       => $order->id,
                'payment_method' => $validated['payment_method'],
                'amount'         => $totalAmount,
                'currency'       => 'EUR',
                'status'         => 'pending',
            ], $cardData));

            // ─── KUPONA STATISTIKAS ATJAUNINĀŠANA ────────────────────────────
            // used_count++ tiek pie subscriber_offers tabulā
            if ($offer && $discountAmount > 0) {
                $offer->incrementUsage();

                \Log::info('Coupon usage incremented', [
                    'code'      => $offer->code,
                    'new_count' => $offer->used_count,
                    'order_id'  => $order->id,
                ]);
            }

            // ─── GROZA IZTĪRĪŠANA ────────────────────────────────────────────
            $cart->items()->delete();

            DB::commit();

            \Log::info('Order creation SUCCESS!', [
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
            ]);

            // ─── APSTIPRINĀJUMA E-PASTS ──────────────────────────────────────
            try {
                Mail::to($order->customer_email)->send(new OrderConfirmation($order, auth()->user()?->locale ?? 'en'));
                \Log::info('Order confirmation email sent', ['order_id' => $order->id]);
            } catch (\Exception $e) {
                \Log::error('Failed to send order confirmation email', [
                    'error'    => $e->getMessage(),
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

    // ─── PRIVĀTIE PALĪGI ─────────────────────────────────────────────────────

    private function generateOrderNumber(): string
    {
        return 'RM-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));
    }

    /**
     * Piegādes izmaksu aprēķins ar pareiziem bezmaksas sliekšņiem.
     *
     * Zonas un cenas (no FAQ + settings tabulas):
     *   Latvija        — bezmaksas virs €35, citādi €3.49
     *   Baltija (EE/LT)— bezmaksas virs €50, citādi €5.49
     *   Pārējā ES      — bezmaksas virs €50, citādi €10.99
     *
     * Slieksnis tiek nolasīts no settings tabulas (free_shipping_threshold = 50),
     * bet Latvijai ir īpašs zemāks slieksnis (35 €).
     */
    private function calculateShipping(string $country, float $subtotal): float
    {
        // Nolasa globālo slieksni (default 50) no settings tabulas
        $globalThreshold = (float) \App\Models\Setting::get('free_shipping_threshold', 50);

        switch ($country) {
            case 'Latvia':
                // Latvijai īpašs slieksnis: bezmaksas no 35 €
                $latviaThreshold = 35.0;
                return $subtotal >= $latviaThreshold ? 0.0 : 3.49;

            case 'Estonia':
            case 'Lithuania':
                // Baltija: bezmaksas no globalThreshold (50 €)
                return $subtotal >= $globalThreshold ? 0.0 : 5.49;

            default:
                // Pārējā ES: bezmaksas no globalThreshold (50 €)
                return $subtotal >= $globalThreshold ? 0.0 : 10.99;
        }
    }

    /**
     * Aprēķina piegādes izmaksas un informāciju par bezmaksas piegādes slieksni.
     * Atgriež masīvu ar cost, threshold, remaining (cik trūkst līdz bezmaksas).
     */
    public static function shippingInfo(string $country, float $subtotal): array
    {
        $globalThreshold = (float) \App\Models\Setting::get('free_shipping_threshold', 50);

        [$cost, $threshold] = match ($country) {
            'Latvia'    => [3.49,  35.0],
            'Estonia',
            'Lithuania' => [5.49,  $globalThreshold],
            default     => [10.99, $globalThreshold],
        };

        $isFree    = $subtotal >= $threshold;
        $remaining = max(0, round($threshold - $subtotal, 2));

        return [
            'cost'      => $isFree ? 0.0 : $cost,
            'is_free'   => $isFree,
            'threshold' => $threshold,
            'remaining' => $remaining, // Cik € vēl jāiepērkas bezmaksas piegādei
        ];
    }

    private function detectCardBrand(string $cardNumber): string
    {
        $n = str_replace(' ', '', $cardNumber);
        if (str_starts_with($n, '4')) return 'Visa';
        if (in_array(substr($n, 0, 2), ['51','52','53','54','55'])) return 'Mastercard';
        if (in_array(substr($n, 0, 2), ['34','37'])) return 'American Express';
        if (in_array(substr($n, 0, 2), ['60','64','65'])) return 'Discover';
        return 'Unknown';
    }

    // ─── PUBLISKIE MARŠRUTI (ROUTES) ───────────────────────────────────────────────────────

    /**
     * atceļ pasūtījumu
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
            return response()->json(['success' => false, 'message' => 'Error cancelling order'], 500);
        }
    }

    // ─── ADMINISTRATORU MARŠRUTI (ROUTES) ────────────────────────────────────────────────────────

    public function adminIndex(): Response
    {
        $orders = Order::with(['user', 'items.product', 'payment'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/Orders/Index', ['orders' => $orders]);
    }

    public function adminShow($id): Response
    {
        $order = Order::with(['user', 'items.product', 'payment', 'courierAssignments.courier'])
            ->findOrFail($id);

        return Inertia::render('Admin/Orders/Show', ['order' => $order]);
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

        return response()->json(['success' => true, 'message' => 'Order status updated', 'order' => $order]);
    }
}
