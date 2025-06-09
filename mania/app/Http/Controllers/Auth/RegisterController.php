<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


//use Illuminate\Database\Query\Builder;
//use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Psy\Readline\Hoa\Console;

class RegisterController extends Controller
{
//    public function register(Request $request)
//    {
//        $validator = Validator::make($request->all(), [
//            'username' => 'required|string|max:255|unique:users',
//            'email' => 'required|email|max:255|unique:users',
//            'password' => 'required|string|min:6|confirmed'
//        ]);
//
//        if ($validator->fails()) {
//            return response()->json(['errors' => $validator->errors()], 422);
//        }
//
//        $user = User::create([
//            'username' => $request->username,
//            'email'    => $request->email,
//            'password' => Hash::make($request->password),
//        ]);
//
//        return response()->json(['message' => 'User registered successfully', 'user' => $user]);
//    }

    public function create(): Response
    {
        return Inertia::render('/RegisterView');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users',
//            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $name = strstr($request->email, '@', true);
        $user = User::create([
            'username' => $name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);
        //auth()->login($user);

        return redirect(route('home', absolute: false));

    }
}
