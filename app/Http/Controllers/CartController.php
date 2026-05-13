<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    /**
     * Parāda iepirkumu grozu
     * Sniedz: resources/js/Pages/Cart/Index.vue
     */
    public function index(): Response
    {
        $cart = Cart::getCurrentCart();
        $cart->load(['items.product']);

        $subtotal = (float) $cart->total_amount;
        $vatRate  = (float) \App\Models\Setting::get('tax_rate', 21);
        $vatAmount = round($subtotal * $vatRate / (100 + $vatRate), 2);

        // Piegādes informācija (bez valsts — parāda abas iespējas)
        $shippingZones = [
            'latvia'  => \App\Http\Controllers\OrderController::shippingInfo('Latvia', $subtotal),
            'baltics' => \App\Http\Controllers\OrderController::shippingInfo('Estonia', $subtotal),
            'eu'      => \App\Http\Controllers\OrderController::shippingInfo('Other', $subtotal),
        ];

        return Inertia::render('Cart/Index', [
            'cart' => [
                'id'              => $cart->id,
                'total_items'     => $cart->total_items,
                'total_amount'    => $subtotal,
                // PVN sadalījums
                'vat_rate'        => $vatRate,
                'vat_amount'      => $vatAmount,
                'subtotal_ex_vat' => round($subtotal - $vatAmount, 2),
            ],
            'items' => $cart->items->map(function ($item) use ($vatRate) {
                $itemVat = round($item->price * $vatRate / (100 + $vatRate), 2);
                return [
                    'id'         => $item->id,
                    'product_id' => $item->product_id,
                    'quantity'   => $item->quantity,
                    'size'       => $item->size,
                    'price'      => $item->price,
                    'price_ex_vat' => round($item->price - $itemVat, 2),
                    'vat_amount'   => $itemVat,
                    'total'      => $item->total,
                    'product'    => [
                        'id'             => $item->product->id,
                        'name_lv'        => $item->product->name_lv,
                        'name_en'        => $item->product->name_en,
                        'slug'           => $item->product->slug,
                        'price'          => $item->product->price,
                        'price_ex_vat'   => $item->product->price_ex_vat,
                        'vat_amount'     => $item->product->vat_amount,
                        'image'          => $item->product->image,
                        'stock_quantity' => $item->product->stock_quantity,
                        'has_sizes'      => (bool) $item->product->has_sizes,
                    ],
                ];
            }),
            'shipping_zones' => $shippingZones,
            'vat_rate'        => $vatRate,
        ]);
    }

    /**
     * Pievieno preci grozā
     */
    public function add(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
            'size'       => 'nullable|string|in:XS,S,M,L,XL,XXL',  // ← PIEVIENOTS
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // Ja produktam ir izmēri un izmērs nav norādīts - atgriež kļūdu
        if ($product->has_sizes && empty($validated['size'])) {
            return response()->json([
                'success' => false,
                'message' => 'Lūdzu izvēlies izmēru / Please select a size',
            ], 422);
        }

        // Krājuma pārbaude
        if ($product->stock_quantity < $validated['quantity']) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock / Nepietiekams daudzums noliktavā',
            ], 400);
        }

        $cart = Cart::getCurrentCart();

        // Meklē esošu grozu rindu pēc product_id un izmēru
        $cartItem = $cart->items()
            ->where('product_id', $validated['product_id'])
            ->where('size', $validated['size'] ?? null)
            ->first();

        if ($cartItem) {
            // Palielina daudzumu
            $newQuantity = $cartItem->quantity + $validated['quantity'];

            if ($product->stock_quantity < $newQuantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot add more items. Stock limit reached / Nevar pievienot vairāk. Sasniegts noliktavas limits',
                ], 400);
            }

            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            // Izveido jaunu grozu rindu
            CartItem::create([
                'cart_id'    => $cart->id,
                'user_id'    => auth()->id(),
                'session_id' => session()->getId(),
                'product_id' => $validated['product_id'],
                'price'      => $product->price,
                'quantity'   => $validated['quantity'],
                'size'       => $validated['size'] ?? null,
            ]);
        }

        $cart->load('items');

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart / Produkts pievienots grozam',
            'cart'    => [
                'total_items'  => $cart->total_items,
                'total_amount' => $cart->total_amount,
            ],
        ]);
    }

    /**
     * Atjaunina groza preču daudzumu
     */
    public function updateQuantity(Request $request, CartItem $cartItem): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::getCurrentCart();
        if ($cartItem->cart_id !== $cart->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized / Nav autorizēts',
            ], 403);
        }

        if ($cartItem->product->stock_quantity < $validated['quantity']) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock / Nepietiekams daudzums',
            ], 400);
        }

        $cartItem->update(['quantity' => $validated['quantity']]);

        $cart->load('items');

        return response()->json([
            'success' => true,
            'message' => 'Quantity updated / Daudzums atjaunināts',
            'item'    => [
                'id'       => $cartItem->id,
                'quantity' => $cartItem->quantity,
                'total'    => $cartItem->total,
            ],
            'cart'    => [
                'total_items'  => $cart->total_items,
                'total_amount' => $cart->total_amount,
            ],
        ]);
    }

    /**
     * Noņem preci no groza
     */
    public function remove(CartItem $cartItem): JsonResponse
    {
        $cart = Cart::getCurrentCart();
        if ($cartItem->cart_id !== $cart->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized / Nav autorizēts',
            ], 403);
        }

        $cartItem->delete();

        $cart->load('items');

        return response()->json([
            'success' => true,
            'message' => 'Item removed / Produkts izņemts',
            'cart'    => [
                'total_items'  => $cart->total_items,
                'total_amount' => $cart->total_amount,
            ],
        ]);
    }

    /**
     * Notīra visu grozu
     */
    public function clear(): JsonResponse
    {
        $cart = Cart::getCurrentCart();
        $cart->clearCart();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared / Grozs iztīrīts',
            'cart'    => [
                'total_items'  => 0,
                'total_amount' => 0,
            ],
        ]);
    }

    /**
     * Iegūst groza skaitu (galvenes nozīmītei)
     */
    public function count(): JsonResponse
    {
        $cart = Cart::getCurrentCart();

        return response()->json([
            'count' => $cart->total_items,
        ]);
    }

    /**
     * Iegūst groza datus (API galapunkts)
     */
    public function get(): JsonResponse
    {
        $cart = Cart::getCurrentCart();
        $cart->load(['items.product']);

        $subtotal  = (float) $cart->total_amount;
        $vatRate   = (float) \App\Models\Setting::get('tax_rate', 21);
        $vatAmount = round($subtotal * $vatRate / (100 + $vatRate), 2);

        return response()->json([
            'cart'  => [
                'id'              => $cart->id,
                'total_items'     => $cart->total_items,
                'total_amount'    => $subtotal,
                'vat_rate'        => $vatRate,
                'vat_amount'      => $vatAmount,
                'subtotal_ex_vat' => round($subtotal - $vatAmount, 2),
            ],
            'items' => $cart->items->map(function ($item) {
                return [
                    'id'           => $item->id,
                    'product_id'   => $item->product_id,
                    'quantity'     => $item->quantity,
                    'size'         => $item->size,
                    'price'        => $item->price,
                    'price_ex_vat' => $item->product->price_ex_vat,
                    'vat_amount'   => $item->product->vat_amount,
                    'total'        => $item->total,
                    'product'      => [
                        'id'      => $item->product->id,
                        'name_lv' => $item->product->name_lv,
                        'name_en' => $item->product->name_en,
                        'slug'    => $item->product->slug,
                        'price'   => $item->product->price,
                        'image'   => $item->product->image,
                    ],
                ];
            }),
        ]);
    }
}
