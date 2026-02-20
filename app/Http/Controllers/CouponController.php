<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    /**
     * POST /api/v1/coupons/validate
     * Validates a coupon code and returns discount info.
     * Does NOT mark it as used yet — that happens on order confirmation.
     */
    public function validate(Request $request)
    {
        $request->validate([
            'code'         => 'required|string|max:64',
            'order_amount' => 'required|numeric|min:0',
        ]);

        $code = strtoupper(trim($request->code));
        $locale = app()->getLocale();

        $coupon = Coupon::active()->where('code', $code)->first();

        if (!$coupon) {
            return response()->json([
                'valid'   => false,
                'message' => $locale === 'lv'
                    ? 'Kupons nav atrasts vai nav derīgs.'
                    : 'Coupon not found or invalid.',
            ], 422);
        }

        $result = $coupon->validate(
            auth()->id(),
            (float) $request->order_amount,
            $locale
        );

        if (!$result['valid']) {
            return response()->json([
                'valid'   => false,
                'message' => $result['message'],
            ], 422);
        }

        return response()->json([
            'valid'          => true,
            'code'           => $coupon->code,
            'type'           => $coupon->type,
            'value'          => (float) $coupon->value,
            'discount'       => $result['discount'],
            'formatted_value'=> $coupon->getFormattedValue(),
            'description'    => $coupon->getDescription($locale),
            'message'        => $result['message'],
        ]);
    }
}
