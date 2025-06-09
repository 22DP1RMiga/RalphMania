<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;

// Home
Route::get('/', function () {
    return Inertia::render('HomeView', [
        'background-image' => '../public/img/Coder_RoltonsLV.png',
        'title' => 'HOME | RalphMania'
    ]);
})->name('home');

// About
Route::get('/about', function () {
    return Inertia::render('AboutView', [
        'background' => '../public/img/Hostage_Adventure.png',
        'overlay' => true,
        'title' => 'ABOUT | RalphMania'
    ]);
})->name('about');

// Contacts
Route::get('/contacts', function () {
    return Inertia::render('ContactsView', [
        'background' => '../public/img/Hostage_Adventure.png',
        'overlay' => true,
        'title' => 'CONTACTS | RalphMania'
    ]);
})->name('contacts');

// Shop
Route::get('/shop', function () {
    return Inertia::render('ShopView', [
        'background' => 'white',
        'title' => 'SHOP | RalphMania'
    ]);
})->name('shop');

// Login
Route::get('/login', function () {
    return Inertia::render('LoginView', [
        'background' => 'white',
        'title' => 'LOGIN | RalphMania'
    ]);
})->name('login');

// Register
Route::get('/register', function () {
    return Inertia::render('RegisterView', [
        'background' => 'white',
        'title' => 'REGISTER | RalphMania'
    ]);
})->name('register');

Route::get('/api/is-logged-in', function () {
    return response()->json([
        'isLoggedIn' => auth()->check(),
        'user' => auth()->user(), // Include the authenticated user
    ]);
});

// POST: Register
//Route::post('/register', [RegisterController::class, 'register'])->name('registration');

// POST: Login
//Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');

