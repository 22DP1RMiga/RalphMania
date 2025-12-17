<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ========== PUBLIC ROUTES ==========

// Home Page
Route::get('/', [HomeController::class, 'index'])->name('home');
//Route::get('/', function () {
//    return Inertia::render('Home');
//})->name('home');

// About Page
Route::get('/about', function () {
    return Inertia::render('About');
})->name('about');

// Contact Page
Route::get('/contact', function () {
    return Inertia::render('Contact');
})->name('contact');

// Contact Form Submission
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// Shop Pages
Route::prefix('shop')->group(function () {
    // Shop Home
    Route::get('/', [ProductController::class, 'shopIndex'])->name('shop.index');

    // Product Detail
    Route::get('/product/{slug}', [ProductController::class, 'show'])->name('shop.product.show');

    // Category Products
    Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('shop.category.show');

    // Shop Contact
    Route::get('/contact', function () {
        return Inertia::render('Shop/Contact');
    })->name('shop.contact');

    // Shop FAQ, Shipping, Returns
    Route::get('/faq', function () {
        return Inertia::render('Shop/FAQ');
    })->name('shop.faq');

    Route::get('/shipping', function () {
        return Inertia::render('Shop/Shipping');
    })->name('shop.shipping');

    Route::get('/returns', function () {
        return Inertia::render('Shop/Returns');
    })->name('shop.returns');
});

// Content Pages
Route::prefix('content')->group(function () {
    // Content Home
    Route::get('/', [ContentController::class, 'index'])->name('content.index');

    // Content Detail
    Route::get('/{slug}', [ContentController::class, 'show'])->name('content.show');

    // Content by Type
    Route::get('/type/{type}', [ContentController::class, 'byType'])->name('content.type');
});

// ========== AUTHENTICATED ROUTES ==========

Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Profile Management
    Route::prefix('profile')->group(function () {
        Route::get('/edit', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // Password Change
        Route::get('/password', [ProfileController::class, 'passwordEdit'])->name('profile.password.edit');
        Route::put('/password', [ProfileController::class, 'passwordUpdate'])->name('profile.password.update');

        // Avatar Upload
        Route::post('/avatar', [ProfileController::class, 'avatarUpdate'])->name('profile.avatar.update');
        Route::delete('/avatar', [ProfileController::class, 'avatarDelete'])->name('profile.avatar.delete');

        // Addresses
        Route::get('/addresses', [ProfileController::class, 'addresses'])->name('profile.addresses');
        Route::get('/addresses/create', [ProfileController::class, 'addressCreate'])->name('profile.addresses.create');
        Route::post('/addresses', [ProfileController::class, 'addressStore'])->name('profile.addresses.store');
        Route::get('/addresses/{id}/edit', [ProfileController::class, 'addressEdit'])->name('profile.addresses.edit');
        Route::put('/addresses/{id}', [ProfileController::class, 'addressUpdate'])->name('profile.addresses.update');
        Route::delete('/addresses/{id}', [ProfileController::class, 'addressDelete'])->name('profile.addresses.delete');
    });

    // Cart
//    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
//    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
//    Route::put('/cart/{id}', [CartController::class, 'update'])->name('cart.update');
//    Route::delete('/cart/{id}', [CartController::class, 'remove'])->name('cart.remove');
//    Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');

    // Grozs
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');   // apskata grozu
        Route::post('/add', [CartController::class, 'add'])->name('add');   // pievieno grozā
        Route::patch('/item/{cartItem}', [CartController::class, 'updateQuantity'])->name('update');    // atjaunina daudzumu
        Route::delete('/item/{cartItem}', [CartController::class, 'remove'])->name('remove');   // noņem preci
        Route::delete('/clear', [CartController::class, 'clear'])->name('clear');   // iztukšo grozu
        Route::get('/count', [CartController::class, 'count'])->name('count');      // iegūst grozu skaitu (emblēmai)
        Route::get('/data', [CartController::class, 'get'])->name('get');   // iegūst groza datus (API)
    });

    // Checkout
    Route::get('/checkout', function () {
        return Inertia::render('Shop/Checkout');
    })->name('checkout');

    // Orders
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/{id}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/', [OrderController::class, 'store'])->name('orders.store');
        Route::put('/{id}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    });

    // Reviews
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::put('/reviews/{id}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    // Comments
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::put('/comments/{id}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{id}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // Like content (WEB route for session auth)
    Route::post('/content/{id}/like', [ContentController::class, 'toggleLike'])->name('content.like');
});

// ========== ADMIN ROUTES ==========

Route::middleware(['auth', 'role:administrator'])->prefix('admin')->group(function () {

    // Admin Dashboard
    Route::get('/dashboard', function () {
        return Inertia::render('Admin/Dashboard');
    })->name('admin.dashboard');

    // Product Management
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'adminIndex'])->name('admin.products.index');
        Route::get('/create', [ProductController::class, 'create'])->name('admin.products.create');
        Route::post('/', [ProductController::class, 'store'])->name('admin.products.store');
        Route::get('/{id}/edit', [ProductController::class, 'edit'])->name('admin.products.edit');
        Route::put('/{id}', [ProductController::class, 'update'])->name('admin.products.update');
        Route::delete('/{id}', [ProductController::class, 'destroy'])->name('admin.products.destroy');
    });

    // Category Management
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'adminIndex'])->name('admin.categories.index');
        Route::post('/', [CategoryController::class, 'store'])->name('admin.categories.store');
        Route::put('/{id}', [CategoryController::class, 'update'])->name('admin.categories.update');
        Route::delete('/{id}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');
    });

    // Content Management
    Route::prefix('content')->group(function () {
        Route::get('/', [ContentController::class, 'adminIndex'])->name('admin.content.index');
        Route::get('/create', [ContentController::class, 'create'])->name('admin.content.create');
        Route::post('/', [ContentController::class, 'store'])->name('admin.content.store');
        Route::get('/{id}/edit', [ContentController::class, 'edit'])->name('admin.content.edit');
        Route::put('/{id}', [ContentController::class, 'update'])->name('admin.content.update');
        Route::delete('/{id}', [ContentController::class, 'destroy'])->name('admin.content.destroy');
    });

    // Order Management
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'adminIndex'])->name('admin.orders.index');
        Route::get('/{id}', [OrderController::class, 'adminShow'])->name('admin.orders.show');
        Route::put('/{id}/status', [OrderController::class, 'updateStatus'])->name('admin.orders.status');
    });

    // Review Moderation
    Route::prefix('reviews')->group(function () {
        Route::get('/', [ReviewController::class, 'adminIndex'])->name('admin.reviews.index');
        Route::put('/{id}/approve', [ReviewController::class, 'approve'])->name('admin.reviews.approve');
        Route::put('/{id}/reject', [ReviewController::class, 'reject'])->name('admin.reviews.reject');
    });

    // Comment Moderation
    Route::prefix('comments')->group(function () {
        Route::get('/', [CommentController::class, 'adminIndex'])->name('admin.comments.index');
        Route::put('/{id}/approve', [CommentController::class, 'approve'])->name('admin.comments.approve');
        Route::put('/{id}/reject', [CommentController::class, 'reject'])->name('admin.comments.reject');
    });

    // Contact Messages
    Route::prefix('contacts')->group(function () {
        Route::get('/', [ContactController::class, 'index'])->name('admin.contacts.index');
        Route::get('/{id}', [ContactController::class, 'show'])->name('admin.contacts.show');
        Route::put('/{id}/read', [ContactController::class, 'markAsRead'])->name('admin.contacts.read');
        Route::put('/{id}/reply', [ContactController::class, 'reply'])->name('admin.contacts.reply');
    });

    // User Management
    Route::prefix('users')->group(function () {
        Route::get('/', function () {
            return Inertia::render('Admin/Users/Index');
        })->name('admin.users.index');

        Route::put('/{id}/toggle-active', function ($id) {
            // TODO: Implement user activation toggle
        })->name('admin.users.toggle');
    });
});

// ========== AUTH ROUTES (BREEZE) ==========
// This includes: /login, /register, /logout, /forgot-password, etc.
require __DIR__.'/auth.php';
