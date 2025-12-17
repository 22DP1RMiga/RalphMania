<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Cart;

class MergeGuestCart
{
    /**
     * Handle an incoming request.
     *
     * Merge guest cart with user cart after login
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only run if user just logged in
        if (auth()->check() && session()->has('guest_session_id')) {
            $guestSessionId = session()->get('guest_session_id');

            // Merge guest cart with user cart
            Cart::mergeGuestCart($guestSessionId, auth()->id());

            // Remove session flag
            session()->forget('guest_session_id');
        }

        return $next($request);
    }
}
