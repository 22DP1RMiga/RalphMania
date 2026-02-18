<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use App\Models\SubscriberOffer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class NewsletterController extends Controller
{
    /**
     * Subscribe to newsletter.
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:100',
        ]);

        $email = $request->email;
        $userId = auth()->id();

        // Check if already subscribed
        if (NewsletterSubscriber::isSubscribed($email)) {
            return response()->json([
                'success' => false,
                'already_subscribed' => true,
                'message' => __('newsletter.already_subscribed'),
            ], 200);
        }

        // Subscribe
        $subscriber = NewsletterSubscriber::subscribe($email, $userId);

        // Log activity
        if ($userId) {
            \App\Models\ActivityLog::log('newsletter_subscribed', 'Pieteicās jaunumu saņemšanai: ' . $email);
        }

        return response()->json([
            'success' => true,
            'message' => __('newsletter.subscribed_success'),
        ]);
    }

    /**
     * Unsubscribe from newsletter.
     */
    public function unsubscribe(Request $request, string $token)
    {
        $success = NewsletterSubscriber::unsubscribeByToken($token);

        if ($success) {
            return redirect('/')->with('success', __('newsletter.unsubscribed_success'));
        }

        return redirect('/')->with('error', __('newsletter.unsubscribe_failed'));
    }

    /**
     * Update subscription preferences.
     */
    public function updatePreferences(Request $request)
    {
        $request->validate([
            'receive_news' => 'boolean',
            'receive_promotions' => 'boolean',
            'receive_announcements' => 'boolean',
        ]);

        $user = auth()->user();
        $subscriber = NewsletterSubscriber::where('user_id', $user->id)->first();

        if (!$subscriber) {
            return response()->json([
                'success' => false,
                'message' => 'Nav abonēts',
            ], 404);
        }

        $subscriber->update($request->only([
            'receive_news',
            'receive_promotions',
            'receive_announcements',
        ]));

        return response()->json([
            'success' => true,
            'message' => __('newsletter.preferences_updated'),
        ]);
    }

    /**
     * Get subscriber offers for authenticated user.
     */
    public function getOffers(Request $request)
    {
        $user = auth()->user();

        // Check if user is subscribed
        $isSubscribed = NewsletterSubscriber::where('user_id', $user->id)
            ->where('is_active', true)
            ->exists();

        if (!$isSubscribed) {
            return response()->json([
                'subscribed' => false,
                'offers' => [],
            ]);
        }

        // Get active offers for subscribers
        $locale = $request->header('Accept-Language', 'lv');
        $offers = SubscriberOffer::active()
            ->forSubscribers()
            ->get()
            ->map(function ($offer) use ($locale) {
                return [
                    'id' => $offer->id,
                    'code' => $offer->code,
                    'title' => $offer->getTitle($locale),
                    'description' => $offer->getDescription($locale),
                    'discount' => $offer->getFormattedDiscount(),
                    'discount_type' => $offer->discount_type,
                    'discount_value' => $offer->discount_value,
                    'min_order' => $offer->min_order_amount,
                    'expires_at' => $offer->expires_at?->format('d.m.Y'),
                ];
            });

        return response()->json([
            'subscribed' => true,
            'offers' => $offers,
        ]);
    }

    /**
     * Check subscription status.
     */
    public function status(Request $request)
    {
        $user = auth()->user();

        $subscriber = NewsletterSubscriber::where('user_id', $user->id)->first();

        return response()->json([
            'subscribed' => $subscriber && $subscriber->is_active,
            'preferences' => $subscriber ? [
                'receive_news' => $subscriber->receive_news,
                'receive_promotions' => $subscriber->receive_promotions,
                'receive_announcements' => $subscriber->receive_announcements,
            ] : null,
        ]);
    }
}
