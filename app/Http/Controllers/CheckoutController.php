<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    /**
     * Show checkout page with cart data
     *
     * GET /checkout
     */
    public function index()
    {
        $user = auth()->user();

        $cart = Cart::where('user_id', $user->id)
            ->with('items.product')
            ->first();

        // Pass user data for pre-filling
        $userData = [
            'name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
            'email' => $user->email,
            'phone' => $user->phone ?? '',
            'country' => $user->country ?? 'Latvia',
            'city' => $user->city ?? '',
            'address' => $user->address ?? '',
            'postal_code' => $user->postal_code ?? '',
        ];

        return Inertia::render('Shop/Checkout', [
            'cart' => $cart,
            'user' => $userData,
        ]);
    }
}
