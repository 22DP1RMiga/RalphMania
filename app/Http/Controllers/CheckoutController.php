<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    /**
     * Rāda norēķināšanās lapu ar groza datiem
     *
     * GET /checkout
     */
    public function index()
    {
        $user = auth()->user();

        $cart = Cart::where('user_id', $user->id)
            ->with('items.product')
            ->first();

        // Nodod lietotāja datus iepriekšējai aizpildīšanai
        $userData = [
            'name'         => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
            'email'        => $user->email,
            'phone'        => $user->phone ?? '',
            'country'      => $user->country ?? 'Latvia',
            'city'         => $user->city ?? '',
            'address'      => $user->address ?? '',
            'postal_code'  => $user->postal_code ?? '',
        ];

        // ── PVN INFORMĀCIJA ──────────────────────────────────────────────────────────
        $vatRate = (float) Setting::get('tax_rate', 21);
        $subtotal = $cart ? (float) $cart->total_amount : 0.0;
        $vatAmount = round($subtotal * $vatRate / (100 + $vatRate), 2);

        // ── PIEGĀDES ZONAS ────────────────────────────────────────────────────
        // Katrai zonai parāda cenu un bezmaksas slieksni
        $shippingZones = [
            [
                'countries' => ['Latvia'],
                'label_lv'  => 'Latvija',
                'label_en'  => 'Latvia',
                'cost'      => 3.49,
                'free_from' => 35.0,
            ],
            [
                'countries' => ['Estonia', 'Lithuania'],
                'label_lv'  => 'Igaunija, Lietuva',
                'label_en'  => 'Estonia, Lithuania',
                'cost'      => 5.49,
                'free_from' => (float) Setting::get('free_shipping_threshold', 50),
            ],
            [
                'countries' => ['other'],
                'label_lv'  => 'Pārējā ES',
                'label_en'  => 'Rest of EU',
                'cost'      => 10.99,
                'free_from' => (float) Setting::get('free_shipping_threshold', 50),
            ],
        ];

        return Inertia::render('Shop/Checkout', [
            'cart'           => $cart,
            'user'           => $userData,
            'vat_rate'       => $vatRate,
            'vat_amount'     => $vatAmount,
            'subtotal_ex_vat'=> round($subtotal - $vatAmount, 2),
            'shipping_zones' => $shippingZones,
        ]);
    }
}
