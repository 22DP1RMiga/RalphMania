<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CartController extends Controller
{
    /**
     * Get or create session ID for guest users
     */
    private function getSessionId()
    {
        if (!session()->has('cart_session_id')) {
            session(['cart_session_id' => Str::uuid()->toString()]);
        }
        return session('cart_session_id');
    }

    /**
     * Get cart items count (for navbar badge)
     * GET /api/cart/count
     */
    public function count()
    {
        $userId = auth()->id();
        $sessionId = $this->getSessionId();

        $count = Cart::where(function($query) use ($userId, $sessionId) {
            if ($userId) {
                $query->where('user_id', $userId);
            } else {
                $query->where('session_id', $sessionId);
            }
        })
            ->sum('quantity');

        return response()->json(['count' => $count ?? 0]);
    }

    /**
     * Display cart page
     * GET /cart
     */
    public function index()
    {
        $userId = auth()->id();
        $sessionId = $this->getSessionId();

        $cartItems = Cart::with('product')
            ->where(function($query) use ($userId, $sessionId) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('session_id', $sessionId);
                }
            })
            ->get();

        $subtotal = $cartItems->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });

        $shipping = $cartItems->count() > 0 ? 5.00 : 0;
        $total = $subtotal + $shipping;

        return Inertia::render('Cart/Index', [
            'cart' => [
                'items' => $cartItems->map(fn($item) => [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'name_lv' => $item->product->name_lv,
                    'name_en' => $item->product->name_en,
                    'image' => $item->product->image,
                    'price' => (float) $item->product->price,
                    'quantity' => $item->quantity,
                    'stock_quantity' => $item->product->stock_quantity,
                    'total' => $item->quantity * (float) $item->product->price,
                ]),
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'total' => $total,
                'count' => $cartItems->sum('quantity'),
            ]
        ]);
    }

    /**
     * Add product to cart
     * POST /cart/add
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        // Check stock
        if ($product->stock_quantity < ($request->quantity ?? 1)) {
            return response()->json([
                'success' => false,
                'message' => 'Not enough stock'
            ], 400);
        }

        $userId = auth()->id();
        $sessionId = $this->getSessionId();

        // Check if item already in cart
        $cartItem = Cart::where('product_id', $request->product_id)
            ->where(function($query) use ($userId, $sessionId) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('session_id', $sessionId);
                }
            })
            ->first();

        if ($cartItem) {
            // Update quantity
            $newQuantity = $cartItem->quantity + ($request->quantity ?? 1);

            if ($product->stock_quantity < $newQuantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not enough stock'
                ], 400);
            }

            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            // Create new cart item
            Cart::create([
                'user_id' => $userId,
                'session_id' => $sessionId,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity ?? 1,
            ]);
        }

        // Get updated count
        $count = Cart::where(function($query) use ($userId, $sessionId) {
            if ($userId) {
                $query->where('user_id', $userId);
            } else {
                $query->where('session_id', $sessionId);
            }
        })
            ->sum('quantity');

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart',
            'count' => $count
        ]);
    }

    /**
     * Update cart item quantity
     * PUT /cart/{id}
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $userId = auth()->id();
        $sessionId = $this->getSessionId();

        $cartItem = Cart::where('id', $id)
            ->where(function($query) use ($userId, $sessionId) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('session_id', $sessionId);
                }
            })
            ->firstOrFail();

        $product = Product::findOrFail($cartItem->product_id);

        // Check stock
        if ($product->stock_quantity < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Not enough stock'
            ], 400);
        }

        $cartItem->update(['quantity' => $request->quantity]);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated'
        ]);
    }

    /**
     * Remove item from cart
     * DELETE /cart/{id}
     */
    public function remove($id)
    {
        $userId = auth()->id();
        $sessionId = $this->getSessionId();

        $cartItem = Cart::where('id', $id)
            ->where(function($query) use ($userId, $sessionId) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('session_id', $sessionId);
                }
            })
            ->firstOrFail();

        $cartItem->delete();

        // Get updated count
        $count = Cart::where(function($query) use ($userId, $sessionId) {
            if ($userId) {
                $query->where('user_id', $userId);
            } else {
                $query->where('session_id', $sessionId);
            }
        })
            ->sum('quantity');

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart',
            'count' => $count
        ]);
    }

    /**
     * Clear cart
     * DELETE /cart
     */
    public function clear()
    {
        $userId = auth()->id();
        $sessionId = $this->getSessionId();

        Cart::where(function($query) use ($userId, $sessionId) {
            if ($userId) {
                $query->where('user_id', $userId);
            } else {
                $query->where('session_id', $sessionId);
            }
        })
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared',
            'count' => 0
        ]);
    }
}
