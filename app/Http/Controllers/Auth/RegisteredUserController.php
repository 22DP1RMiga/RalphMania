<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Helpers\LocaleHelper;
use App\Models\User;
use App\Mail\VerifyEmailMail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Pieļaujamie e-pasta domēni - regulārā izteiksme.
     * Pieļauj: gmail, outlook, hotmail, live, yahoo, icloud, me, mac,
     * proton, protonmail, zoho, tutanota, gmx, inbox, aol, yandex,
     * plus Latvijas: inbox.lv, apollo.lv, tvnet.lv, one.lv, e-apollo.lv
     */
    private const EMAIL_DOMAIN_REGEX =
        '/^[a-zA-Z0-9._%+\-]+@(' .
        'gmail\.com|' .
        'outlook\.com|outlook\.lv|' .
        'hotmail\.com|hotmail\.lv|hotmail\.co\.uk|' .
        'live\.com|live\.lv|' .
        'yahoo\.com|yahoo\.co\.uk|yahoo\.lv|' .
        'icloud\.com|me\.com|mac\.com|' .
        'proton\.me|protonmail\.com|protonmail\.ch|' .
        'zoho\.com|' .
        'tutanota\.com|tuta\.io|' .
        'gmx\.com|gmx\.net|gmx\.de|' .
        'inbox\.com|' .
        'aol\.com|' .
        'yandex\.com|yandex\.ru|ya\.ru|' .
        'inbox\.lv|' .
        'apollo\.lv|' .
        'tvnet\.lv|' .
        'one\.lv|' .
        'e-apollo\.lv' .
        ')$/i';

    /**
     * Parāda reģistrācijas skatu
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Apstrādā ienākošo reģistrācijas pieprasījumu
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $locale = $request->input('locale', 'lv');
        LocaleHelper::set($locale);
        $isLv = $locale === 'lv';

        $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name'  => 'required|string|max:50',
            'username'   => 'required|string|max:30|unique:' . User::class,
            'locale'     => 'nullable|string|in:lv,en',
            // ── E-pasts ────────────────────────────────────────────────────────
            // 1. Standarta e-pasta formāts
            // 2. Jābūt mazo burtu formātā (lowercase)
            // 3. RegEx pārbauda pieļaujamos domēnus
            'email' => [
                'required',
                'string',
                'lowercase',
                'email:rfc,dns',
                'max:100',
                'unique:' . User::class,
                'regex:' . self::EMAIL_DOMAIN_REGEX,
            ],

            // ── Parole ─────────────────────────────────────────────────────────
            // min 8 | vismaz 1 mazs burts | vismaz 1 liels burts
            // | vismaz 1 cipars | vismaz 1 speciālais simbols
            'password' => [
                'required',
                'confirmed',
                Rules\Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
        ], [
            // ── Kļūdu ziņojumi latviešu/angļu valodā ────────────────────────────────
            'email.regex' => $isLv
                ? 'Lūdzu izmantojiet atpazīstamu e-pasta pakalpojumu (piemēram, Gmail, Outlook, iCloud u.c.).'
                : 'Please use a recognised email provider (e.g. Gmail, Outlook, iCloud, etc.).',

            'password.min' => $isLv
                ? 'Parolei jāsastāv no vismaz 8 simboliem.'
                : 'Password must be at least 8 characters.',

            'password.mixed_case' => $isLv
                ? 'Parolē jābūt vismaz vienam lielajam un vienam mazajam burtam.'
                : 'Password must contain at least one uppercase and one lowercase letter.',

            'password.letters' => $isLv
                ? 'Parolē jāietver vismaz viens burts.'
                : 'Password must contain at least one letter.',

            'password.numbers' => $isLv
                ? 'Parolē jāietver vismaz viens cipars.'
                : 'Password must contain at least one number.',

            'password.symbols' => $isLv
                ? 'Parolē jāietver vismaz viens speciālais simbols (piem., !, @, #, $).'
                : 'Password must contain at least one special character (e.g. !, @, #, $).',

            'password.uncompromised' => $isLv
                ? 'Šī parole ir parādījusies datu noplūdēs. Lūdzu izvēlieties citu.'
                : 'This password has appeared in a data breach. Please choose a different one.',
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'username'   => $request->username,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'locale'     => $locale,
            'role_id'    => 2, // Noklusētā loma: Lietotājs
        ]);

        event(new Registered($user));
        Auth::login($user);
        $this->sendVerificationEmail($user, $locale);

        return redirect(route('dashboard', absolute: false));
    }

    /**
     * Nosūta pielāgotu (custom) verifikācijas e-pastu ar verify-email.blade.php veidni.
     */
    protected function sendVerificationEmail(User $user, string $locale = 'lv'): void
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            ['id' => $user->getKey(), 'hash' => sha1($user->getEmailForVerification())]
        );

        try {
            Mail::to($user->email)->send(
                new VerifyEmailMail(
                    $verificationUrl,
                    $user->first_name ?? ($locale === 'en' ? 'User' : 'Lietotāj'),
                    $locale
                )
            );
            \Log::info('Verification email sent', ['email' => $user->email]);
        } catch (\Exception $e) {
            \Log::error('Failed to send verification email', ['error' => $e->getMessage()]);
        }
    }
}
