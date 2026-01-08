<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
        ]);

        // Get the user
        $user = \App\Models\User::where('email', $request->email)->first();

        if ($user) {
            // Generate reset token
            $token = Password::createToken($user);

            // Generate reset URL
            $resetUrl = url(route('password.reset', [
                'token' => $token,
                'email' => $user->email,
            ], false));

            // Send custom email
            try {
                Mail::to($user->email)->send(
                    new ResetPasswordMail($resetUrl, $user->first_name ?? 'Lietotāj')
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

        // Always return success to prevent email enumeration
        return back()->with('status', __('passwords.sent'));
    }
}
