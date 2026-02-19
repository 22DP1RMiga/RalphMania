<?php

namespace App\Http\Controllers;

use App\Mail\NewsletterWelcome;
use App\Models\NewsletterSubscriber;
use App\Models\SubscriberOffer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class NewsletterController extends Controller
{
    /**
     * Default subscription duration in days.
     * null  = forever (no expiry)
     * integer = days (e.g. 365 = 1 year)
     */
    private const SUBSCRIPTION_DURATION_DAYS = 365; // 1 gads

    // ─── SUBSCRIBE ───────────────────────────────────────────────────────────

    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:100',
        ]);

        $email  = $request->email;
        $userId = auth()->id();
        $user   = auth()->user();

        // Already subscribed (and not expired)
        if (NewsletterSubscriber::isSubscribed($email)) {
            return response()->json([
                'success'            => false,
                'already_subscribed' => true,
                'message'            => __('newsletter.already_subscribed'),
            ]);
        }

        // Create / reactivate subscriber with expiry
        $subscriber = NewsletterSubscriber::subscribe(
            $email,
            $userId,
            self::SUBSCRIPTION_DURATION_DAYS
        );

        // Activity log
        if ($userId) {
            \App\Models\ActivityLog::log(
                'newsletter_subscribed',
                'Pieteicās jaunumu saņemšanai: ' . $email
            );
        }

        // Send welcome email
        try {
            Mail::to($email)->send(new NewsletterWelcome(
                subscriber: $subscriber,
                userName:   $user?->username ?? '',
            ));
        } catch (\Exception $e) {
            // Log but don't fail subscription if mail fails
            \Log::error('Newsletter welcome email failed: ' . $e->getMessage());
        }

        return response()->json([
            'success'    => true,
            'message'    => __('newsletter.subscribed_success'),
            'expires_at' => $subscriber->subscription_expires_at?->format('d.m.Y'),
        ]);
    }

    // ─── UNSUBSCRIBE ─────────────────────────────────────────────────────────

    public function unsubscribe(Request $request, string $token)
    {
        $success = NewsletterSubscriber::unsubscribeByToken($token);

        if ($success) {
            return redirect('/')->with('success', __('newsletter.unsubscribed_success'));
        }

        return redirect('/')->with('error', __('newsletter.unsubscribe_failed'));
    }

    // ─── PREFERENCES ─────────────────────────────────────────────────────────

    public function updatePreferences(Request $request)
    {
        $request->validate([
            'receive_news'          => 'boolean',
            'receive_promotions'    => 'boolean',
            'receive_announcements' => 'boolean',
        ]);

        $subscriber = NewsletterSubscriber::where('user_id', auth()->id())->first();

        if (!$subscriber) {
            return response()->json(['success' => false, 'message' => 'Nav abonēts'], 404);
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

    // ─── STATUS ──────────────────────────────────────────────────────────────

    public function status(Request $request)
    {
        $subscriber = NewsletterSubscriber::where('user_id', auth()->id())->first();

        $isActive = $subscriber
            && $subscriber->is_active
            && !$subscriber->is_expired;

        return response()->json([
            'subscribed'  => $isActive,
            'expires_at'  => $subscriber?->subscription_expires_at?->format('d.m.Y'),
            'days_remaining' => $subscriber?->days_remaining,
            'is_expired'  => $subscriber?->is_expired ?? false,
            'preferences' => $subscriber ? [
                'receive_news'          => $subscriber->receive_news,
                'receive_promotions'    => $subscriber->receive_promotions,
                'receive_announcements' => $subscriber->receive_announcements,
            ] : null,
        ]);
    }

    // ─── OFFERS ──────────────────────────────────────────────────────────────

    public function getOffers(Request $request)
    {
        $subscriber = NewsletterSubscriber::where('user_id', auth()->id())
            ->where('is_active', true)
            ->first();

        // Check active and not expired
        if (!$subscriber || $subscriber->is_expired) {
            return response()->json([
                'subscribed' => false,
                'offers'     => [],
            ]);
        }

        $locale = app()->getLocale();
        $offers = SubscriberOffer::active()
            ->forSubscribers()
            ->get()
            ->map(fn($offer) => [
                'id'            => $offer->id,
                'code'          => $offer->code,
                'title'         => $offer->getTitle($locale),
                'description'   => $offer->getDescription($locale),
                'discount'      => $offer->getFormattedDiscount(),
                'discount_type' => $offer->discount_type,
                'discount_value'=> $offer->discount_value,
                'min_order'     => $offer->min_order_amount,
                'expires_at'    => $offer->expires_at?->format('d.m.Y'),
            ]);

        return response()->json([
            'subscribed' => true,
            'offers'     => $offers,
        ]);
    }
}
