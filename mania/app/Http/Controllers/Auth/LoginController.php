<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;


use App\Http\Requests\Auth\LoginRequest;
use App\Models\Admins;
use Illuminate\Http\RedirectResponse;


use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // If the input is a username, modify credentials to search by username
        if (filter_var($credentials['email'], FILTER_VALIDATE_EMAIL)) {  // Fixed: Added closing parenthesis
            $user = User::where('email', $credentials['email'])->first();
        } else {
            $user = User::where('username', $credentials['email'])->first();
        }

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        return response()->json(['message' => 'Login successful', 'user' => $user]);
    }
//        $user = User::where('email', $credentials['email'])
//            ->orWhere('username', $credentials['username'])
//            ->first();
//
//        if (!$user || !Hash::check($credentials['password'], $user->password)) {
//            return response()->json(['error' => 'Invalid credentials'], 401);
//        }
//
//        return response()->json(['message' => 'Login successful', 'user' => $user]);

    public function create(): Response
    {
        return Inertia::render('/LoginView', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('home', absolute: false));
    }
}
