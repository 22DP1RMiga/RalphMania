<?php

namespace App\Http\Controllers;

use App\Models\SubscriberOffer;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CouponController extends Controller
{
    /**
     * Validē kupona kodu un atgriež atlaides informāciju.
     *
     * POST /coupons/validate
     * Body: { code: "WINTER25", order_amount: 49.99 }
     */
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'code'         => 'required|string|max:64',
            'order_amount' => 'required|numeric|min:0',
        ]);

        $code        = strtoupper(trim($request->code));
        $orderAmount = (float) $request->order_amount;
        $locale      = app()->getLocale(); // 'lv' vai 'en'

        // Meklē aktīvu piedāvājumu
        $offer = SubscriberOffer::active()
            ->where('code', $code)
            ->first();

        if (!$offer) {
            return $this->invalid($locale === 'lv'
                ? 'Kupons nav atrasts vai nav derīgs.'
                : 'Coupon not found or invalid.'
            );
        }

        // Pārbauda minimālo pasūtījuma summu
        if ($offer->min_order_amount && $orderAmount < $offer->min_order_amount) {
            $min = number_format($offer->min_order_amount, 2);
            return $this->invalid($locale === 'lv'
                ? "Minimālais pasūtījums šim kuponam: €{$min}"
                : "Minimum order for this coupon: €{$min}"
            );
        }

        // Pārbauda vai kupons ir tikai abonentiem
        if ($offer->subscribers_only) {
            $isSubscribed = NewsletterSubscriber::where('user_id', auth()->id())
                ->where('is_active', true)
                ->where('is_verified', true)
                ->where(function ($q) {
                    $q->whereNull('subscription_expires_at')
                        ->orWhere('subscription_expires_at', '>=', now());
                })
                ->exists();

            if (!$isSubscribed) {
                return $this->invalid($locale === 'lv'
                    ? 'Šis kupons ir pieejams tikai jaunumu abonentiem.'
                    : 'This coupon is available to newsletter subscribers only.'
                );
            }
        }

        // Aprēķina atlaidi
        $discount = $this->calculateDiscount($offer, $orderAmount);

        $description = $locale === 'lv'
            ? ($offer->description_lv ?? '')
            : ($offer->description_en ?? '');

        $message = $locale === 'lv'
            ? "Kupons pielietots! Atlaide: €" . number_format($discount, 2)
            : "Coupon applied! Discount: €" . number_format($discount, 2);

        return response()->json([
            'valid'           => true,
            'code'            => $offer->code,
            'type'            => $offer->discount_type,        // 'percentage' vai 'fixed'
            'value'           => (float) $offer->discount_value,
            'discount'        => $discount,                     // € summa
            'formatted_value' => $offer->getFormattedDiscount(), // '10%' vai '€5.00'
            'description'     => $description,
            'message'         => $message,
        ]);
    }

    // ─── PRIVĀTĀS METODES ────────────────────────────────────────────────────

    /**
     * Aprēķina faktisko atlaidi €.
     */
    private function calculateDiscount(SubscriberOffer $offer, float $orderAmount): float
    {
        if ($offer->discount_type === 'percentage') {
            $discount = round($orderAmount * ($offer->discount_value / 100), 2);
        } else {
            // Fiksēta summa — nevar pārsniegt pasūtījuma vērtību
            $discount = min((float) $offer->discount_value, $orderAmount);
        }

        return $discount;
    }

    /**
     * Atgriež JSON kļūdas atbildi.
     */
    private function invalid(string $message): JsonResponse
    {
        return response()->json([
            'valid'   => false,
            'message' => $message,
        ], 422);
    }
}
