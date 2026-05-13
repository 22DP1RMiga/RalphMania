<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Parāda pieteikšanās skatu
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Apstrādā ienākošo autentifikācijas pieprasījumu
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Atjaunina pēdējās pieteikšanās laika zīmogu
        $user = Auth::user();
        if ($user && method_exists($user, 'updateLastLogin')) {
            try {
                $user->updateLastLogin();
            } catch (\Exception $e) {
                // Pa kluso kļūda, ja kolonna vēl nepastāv
                // Tas novērš kļūdas migrācijas procesa laikā
            }
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Iznīcina autentificētu sesiju
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
