<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Helpers\LocaleHelper;
use App\Mail\ResetPasswordMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'locale' => 'nullable|string|in:lv,en',
        ]);

        // Nodrošina lokalizāciju
        $locale = $request->input('locale', 'lv');
        LocaleHelper::set($locale);

        // Get the user
        $user = \App\Models\User::where('email', $request->email)->first();

        if ($user) {
            // Ģenerē atiestatīšanas tokenu
            $token = Password::createToken($user);

            // Ģenerē atiestatīšanas URL
            $resetUrl = url(route('password.reset', [
                'token' => $token,
                'email' => $user->email,
            ], false));

            // Nosūta pielāgotu (custom) e-pastu
            try {
                Mail::to($user->email)->send(
                    new ResetPasswordMail(
                        $resetUrl,
                        $user->first_name ?? ($locale === 'en' ? 'User' : 'Lietotāj'),
                        $locale
                    )
                );
                \Log::info('Password reset email sent', [
                    'email' => $user->email,
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to send password reset email', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Vienmēr atgriež veiksmīgu rezultātu, lai novērstu e-pasta uzskaitīšanu.
        return back()->with('status', __('passwords.sent'));
    }
}
