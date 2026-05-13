<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Helpers\LocaleHelper;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class NewPasswordController extends Controller
{
    /**
     * Parāda paroles atiestatīšanas skatu
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Auth/ResetPassword', [
            'email' => $request->email,
            'token' => $request->route('token'),
        ]);
    }

    /**
     * Apstrādā ienākošo jaunas paroles pieprasījumu
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'locale'   => 'nullable|string|in:lv,en',
        ]);

        // Nodrošina lokalizāciju
        $locale = $request->input('locale', 'lv');
        LocaleHelper::set($locale);

        // Šeit mēģina atiestatīt lietotāja paroli. Ja tas būs veiksmīgi, tad
        // atjauninās paroli faktiskā lietotāja modelī un saglabās to datubāzē.
        // Pretējā gadījumā parsēs kļūdu un atgriezīs atbildi.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request, $locale) {
                $user->forceFill([
                    'password'       => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                    'locale'         => $locale,
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // Ja parole tika veiksmīgi atiestatīta, tad novirzīs lietotāju atpakaļ uz
        // lietojumprogrammas sākumlapas autentificēto skatu. Ja rodas kļūda, tad var viņu
        // novirzīt atpakaļ uz vietu, no kuras lietotājs ieradās ar kļūdas ziņojumu.
        if ($status == Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', __($status));
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }
}
