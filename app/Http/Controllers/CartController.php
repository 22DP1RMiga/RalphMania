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
     * Display the shopping cart
     * Renders: resources/js/Pages/Cart/Index.vue
     */
    public function index(): Response
    {
        $cart = Cart::getCurrentCart();
        $cart->load(['items.product']);

        return Inertia::render('Cart/Index', [
            'cart' => [
                'id'           => $cart->id,
                'total_items'  => $cart->total_items,
                'total_amount' => $cart->total_amount,
            ],
            'items' => $cart->items->map(function ($item) {
                return [
                    'id'         => $item->id,
                    'product_id' => $item->product_id,
                    'quantity'   => $item->quantity,
                    'size'       => $item->size,    // ← PIEVIENOTS
                    'price'      => $item->price,
                    'total'      => $item->total,
                    'product'    => [
                        'id'             => $item->product->id,
                        'name_lv'        => $item->product->name_lv,
                        'name_en'        => $item->product->name_en,
                        'slug'           => $item->product->slug,
                        'price'          => $item->product->price,
                        'image'          => $item->product->image,
                        'stock_quantity' => $item->product->stock_quantity,
                        'has_sizes'      => (bool) $item->product->has_sizes, // ← PIEVIENOTS
                    ],
                ];
            }),
        ]);
    }

    /**
     * Add item to cart
     */
    public function add(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
            'size'       => 'nullable|string|in:XS,S,M,L,XL,XXL',  // ← PIEVIENOTS
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // Ja produktam ir izmēri un izmērs nav norādīts — atgriež kļūdu
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

        // Meklē esošu grozu rindu pēc product_id UN size
        // (S un L ir DIVAS atsevišķas rindas — nevis viena)
        $cartItem = $cart->items()
            ->where('product_id', $validated['product_id'])
            ->where('size', $validated['size'] ?? null)  // ← PIEVIENOTS: size jāatbilst
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
                'size'       => $validated['size'] ?? null,  // ← PIEVIENOTS
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
     * Update cart item quantity
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
     * Remove item from cart
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
     * Clear entire cart
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
     * Get cart count (for header badge)
     */
    public function count(): JsonResponse
    {
        $cart = Cart::getCurrentCart();

        return response()->json([
            'count' => $cart->total_items,
        ]);
    }

    /**
     * Get cart data (API endpoint)
     */
    public function get(): JsonResponse
    {
        $cart = Cart::getCurrentCart();
        $cart->load(['items.product']);

        return response()->json([
            'cart'  => [
                'id'           => $cart->id,
                'total_items'  => $cart->total_items,
                'total_amount' => $cart->total_amount,
            ],
            'items' => $cart->items->map(function ($item) {
                return [
                    'id'         => $item->id,
                    'product_id' => $item->product_id,
                    'quantity'   => $item->quantity,
                    'size'       => $item->size,    // ← PIEVIENOTS
                    'price'      => $item->price,
                    'total'      => $item->total,
                    'product'    => [
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
