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
                'id' => $cart->id,
                'total_items' => $cart->total_items,
                'total_amount' => $cart->total_amount,
            ],
            'items' => $cart->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->total,
                    'product' => [
                        'id' => $item->product->id,
                        'name_lv' => $item->product->name_lv,
                        'name_en' => $item->product->name_en,
                        'slug' => $item->product->slug,
                        'price' => $item->product->price,
                        'image' => $item->product->image,
                        'stock_quantity' => $item->product->stock_quantity,
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
            'quantity' => 'required|integer|min:1',
        ]);

        // Get product
        $product = Product::findOrFail($validated['product_id']);

        // Check stock
        if ($product->stock_quantity < $validated['quantity']) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock / Nepietiekams daudzums noliktavā'
            ], 400);
        }

        // Get or create cart
        $cart = Cart::getCurrentCart();

        // Check if item already exists in cart
        $cartItem = $cart->items()
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($cartItem) {
            // Update quantity
            $newQuantity = $cartItem->quantity + $validated['quantity'];

            // Check stock again
            if ($product->stock_quantity < $newQuantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot add more items. Stock limit reached / Nevar pievienot vairāk. Sasniegts noliktavas limits'
                ], 400);
            }

            $cartItem->update([
                'quantity' => $newQuantity,
            ]);
        } else {
            // Create new cart item
            CartItem::create([
                'cart_id' => $cart->id,
                'user_id' => auth()->id(),
                'session_id' => session()->getId(),
                'product_id' => $validated['product_id'],
                'price' => $product->price,
                'quantity' => $validated['quantity'],
            ]);
        }

        // Reload cart with items
        $cart->load('items');

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart / Produkts pievienots grozam',
            'cart' => [
                'total_items' => $cart->total_items,
                'total_amount' => $cart->total_amount,
            ]
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

        // Check if item belongs to current user's cart
        $cart = Cart::getCurrentCart();
        if ($cartItem->cart_id !== $cart->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized / Nav autorizēts'
            ], 403);
        }

        // Check stock
        if ($cartItem->product->stock_quantity < $validated['quantity']) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock / Nepietiekams daudzums'
            ], 400);
        }

        $cartItem->update([
            'quantity' => $validated['quantity'],
        ]);

        // Reload cart
        $cart->load('items');

        return response()->json([
            'success' => true,
            'message' => 'Quantity updated / Daudzums atjaunināts',
            'item' => [
                'id' => $cartItem->id,
                'quantity' => $cartItem->quantity,
                'total' => $cartItem->total,
            ],
            'cart' => [
                'total_items' => $cart->total_items,
                'total_amount' => $cart->total_amount,
            ]
        ]);
    }

    /**
     * Remove item from cart
     */
    public function remove(CartItem $cartItem): JsonResponse
    {
        // Check if item belongs to current user's cart
        $cart = Cart::getCurrentCart();
        if ($cartItem->cart_id !== $cart->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized / Nav autorizēts'
            ], 403);
        }

        $cartItem->delete();

        // Reload cart
        $cart->load('items');

        return response()->json([
            'success' => true,
            'message' => 'Item removed / Produkts izņemts',
            'cart' => [
                'total_items' => $cart->total_items,
                'total_amount' => $cart->total_amount,
            ]
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
            'cart' => [
                'total_items' => 0,
                'total_amount' => 0,
            ]
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
            'cart' => [
                'id' => $cart->id,
                'total_items' => $cart->total_items,
                'total_amount' => $cart->total_amount,
            ],
            'items' => $cart->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->total,
                    'product' => [
                        'id' => $item->product->id,
                        'name_lv' => $item->product->name_lv,
                        'name_en' => $item->product->name_en,
                        'slug' => $item->product->slug,
                        'price' => $item->product->price,
                        'image' => $item->product->image,
                    ],
                ];
            }),
        ]);
    }
}
