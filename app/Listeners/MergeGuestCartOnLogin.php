<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\Cart;

class MergeGuestCartOnLogin
{
    /**
     * Vada pasākumu
     *
     * Apvieno viesa grozu ar lietotāja grozu, kad lietotājs piesakās
     */
    public function handle(Login $event): void
    {
        // Iegūst sesijas ID pirms pieteikšanās
        $sessionId = session()->getId();

        // Iegūst lietotāja ID
        $userId = $event->user->id;

        // Apvieno viesa grozu ar lietotāja grozu
        Cart::mergeGuestCart($sessionId, $userId);
    }
}
