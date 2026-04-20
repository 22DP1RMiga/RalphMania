<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Kļūdu ziņojumi atkarībā no valodas.
     */
    private function msg(string $lv, string $en): string
    {
        $locale = app()->getLocale();
        return $locale === 'lv' ? $lv : $en;
    }

    /**
     * Autentificē pieprasījumu ar detalizētiem kļūdu ziņojumiem.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $isEmail   = filter_var($this->login, FILTER_VALIDATE_EMAIL);
        $loginType = $isEmail ? 'email' : 'username';

        // ── 1. Pārbauda vai lietotājs vispār eksistē ─────────────────
        $user = User::where($loginType, $this->login)->first();

        if (!$user) {
            RateLimiter::hit($this->throttleKey());

            $errorKey = $isEmail ? 'login' : 'login';
            $message  = $isEmail
                ? $this->msg(
                    'Lietotājs ar šādu e-pasta adresi netika atrasts.',
                    'No account found with this email address.'
                )
                : $this->msg(
                    'Lietotājs ar šādu lietotājvārdu netika atrasts.',
                    'No account found with this username.'
                );

            throw ValidationException::withMessages([
                'login' => $message,
            ]);
        }

        // ── 2. Pārbauda vai konts nav aizliegts PIRMS paroles pārbaudes ──
        if (!$user->is_active) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => $this->msg(
                    'Jūsu konts ir deaktivizēts. Sazinieties ar administratoru, ja uzskatāt, ka tas ir kļūda.',
                    'Your account has been suspended. Please contact an administrator if you believe this is a mistake.'
                ),
            ]);
        }

        // ── 3. Pārbauda paroli ────────────────────────────────────────
        if (!Auth::attempt([$loginType => $this->login, 'password' => $this->password], $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'password' => $this->msg(
                    'Nepareiza parole. Lūdzu pārbaudiet un mēģiniet vēlreiz.',
                    'Incorrect password. Please check and try again.'
                ),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Rate limiting ar lokalizētiem ziņojumiem.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());
        $minutes = ceil($seconds / 60);

        throw ValidationException::withMessages([
            'login' => $this->msg(
                "Pārāk daudz pieteikšanās mēģinājumu. Lūdzu mēģiniet vēlreiz pēc {$minutes} min.",
                "Too many login attempts. Please try again in {$minutes} minute(s)."
            ),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('login')) . '|' . $this->ip());
    }
}
