<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    // User Registration
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

        // Generate token for the user
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful!',
            'token' => $token,
            'user' => $user
            ], 201);
    }

    public function login(Request $request)
    {
        // Validate request data
        $request->validate([
            'user_input' => 'required', // Username or Email
            'password' => 'required',
        ], [
            'user_input.required' => 'Username or Email is required!',
            'password.required' => 'Password is required!',
        ]);

        // Find user by username
        $user = User::where('username', $request->user_input)
            ->orWhere('email', $request->user_input)
            ->first();

        // Check if user exists and password is correct
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid username/email or password!'], 401);
        }

        // Generate token for the user
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful!',
            'token' => $token,
            'user' => $user
        ], 200);
    }

    // User Logout
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully!'], 200);
    }
}
