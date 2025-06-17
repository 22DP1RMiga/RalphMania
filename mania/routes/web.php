<?php

// Web stuff
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// START PAGE
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// DASHBOARD
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// AUTHENTICATION
Route::middleware('auth')->group(function () {
    Route::get('/profile', [Controller::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [Controller::class, 'update'])->name('profile.update');
    Route::delete('/profile', [Controller::class, 'destroy'])->name('profile.destroy');
});

// HOME PAGE
Route::get('/home', function () {
    return Inertia::render('HomeView');
});

// ABOUT PAGE
Route::get('/about', function () {
    return Inertia::render('AboutView');
});

// CONTACTS PAGE
Route::get('/contacts', function () {
    return Inertia::render('ContactsView');
})->middleware(['auth']);

// SHOP PAGE
Route::get('/shop', function () {
    return Inertia::render('ShopView');
});

// TEST API ROUTE
Route::get('/api/test-web', function () {
    return response()->json(['message' => 'WEB ROUTE is working']);
});


require __DIR__.'/auth.php';
