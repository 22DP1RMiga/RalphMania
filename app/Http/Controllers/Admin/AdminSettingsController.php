<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class AdminSettingsController extends Controller
{
    /**
     * Display settings page.
     */
    public function index()
    {
        // Get all settings as key-value pairs
        $settings = Setting::pluck('value', 'key')->toArray();

        return Inertia::render('Admin/Settings/Index', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update settings.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // General
            'site_name' => 'nullable|string|max:255',
            'site_description_lv' => 'nullable|string|max:1000',
            'site_description_en' => 'nullable|string|max:1000',
            'admin_email' => 'nullable|email|max:255',
            'timezone' => 'nullable|string|max:100',
            'date_format' => 'nullable|string|max:50',

            // Shop
            'currency' => 'nullable|string|max:10',
            'currency_symbol' => 'nullable|string|max:5',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'free_shipping_threshold' => 'nullable|numeric|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'items_per_page' => 'nullable|integer|min:5|max:100',

            // Email
            'mail_from_name' => 'nullable|string|max:255',
            'mail_from_address' => 'nullable|email|max:255',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',

            // Social
            'facebook_url' => 'nullable|url|max:500',
            'instagram_url' => 'nullable|url|max:500',
            'twitter_url' => 'nullable|url|max:500',
            'youtube_url' => 'nullable|url|max:500',
            'tiktok_url' => 'nullable|url|max:500',

            // SEO
            'meta_title_lv' => 'nullable|string|max:255',
            'meta_title_en' => 'nullable|string|max:255',
            'meta_description_lv' => 'nullable|string|max:500',
            'meta_description_en' => 'nullable|string|max:500',
            'google_analytics_id' => 'nullable|string|max:50',
            'facebook_pixel_id' => 'nullable|string|max:50',
        ]);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        // Clear settings cache
        Cache::forget('settings');

        // Log activity
        ActivityLog::log('settings_updated', 'Iestatījumi atjaunināti');

        return back()->with('success', 'Iestatījumi veiksmīgi saglabāti!');
    }

    /**
     * Send test email.
     */
    public function testEmail(Request $request)
    {
        try {
            $adminEmail = Setting::where('key', 'admin_email')->value('value')
                ?? auth()->user()->email;

            Mail::raw('Šis ir testa e-pasts no jūsu administrācijas paneļa.', function ($message) use ($adminEmail) {
                $message->to($adminEmail)
                    ->subject('Testa e-pasts - RalphMania Admin');
            });

            // Log activity
            ActivityLog::log('test_email_sent', 'Testa e-pasts nosūtīts uz ' . $adminEmail);

            return back()->with('success', 'Testa e-pasts veiksmīgi nosūtīts!');
        } catch (\Exception $e) {
            return back()->with('error', 'Kļūda sūtot e-pastu: ' . $e->getMessage());
        }
    }

    /**
     * Clear application cache.
     */
    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');

            // Log activity
            ActivityLog::log('cache_cleared', 'Kešatmiņa notīrīta');

            return back()->with('success', 'Kešatmiņa veiksmīgi notīrīta!');
        } catch (\Exception $e) {
            return back()->with('error', 'Kļūda notīrot kešatmiņu: ' . $e->getMessage());
        }
    }
}
