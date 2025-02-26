<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validate request data
        $request->validate([
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ], [
            'username.required' => 'Username is required!',
            'password.required' => 'Password is required!',
            'email.required' => 'Email is required!',
            'email.email' => 'Enter a valid email address!',
            'password.confirmed' => 'Passwords do not match!',
        ]);

        // Create and store the user in the database
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'Registration successful!'], 201);
    }

    public function login(Request $request)
    {
        // Validate request data
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ], [
            'username.required' => 'Username is required!',
            'password.required' => 'Password is required!',
        ]);

        // Find user by username
        $user = User::where('username', $request->username)->first();

        // Check if user exists and password is correct
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid username or password!'], 401);
        }

        return response()->json(['message' => 'Login successful!', 'user' => $user], 200);
    }
}
