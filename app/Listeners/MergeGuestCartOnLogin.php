<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\Cart;

class MergeGuestCartOnLogin
{
    /**
     * Handle the event.
     *
     * Merge guest cart with user cart when user logs in
     */
    public function handle(Login $event): void
    {
        // Get session ID before login
        $sessionId = session()->getId();

        // Get user ID
        $userId = $event->user->id;

        // Merge guest cart with user cart
        Cart::mergeGuestCart($sessionId, $userId);
    }
}
