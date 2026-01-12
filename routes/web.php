<?php
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminContentController;
use App\Http\Controllers\Admin\AdminReviewController;
use App\Http\Controllers\Admin\AdminCommentController;
use App\Http\Controllers\Admin\AdminContactController;
use App\Http\Controllers\Admin\AdminUserController;
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
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\InvoiceController;
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

// About Page
Route::get('/about', function () {
    return Inertia::render('About');
})->name('about');

// Contact Page - REQUIRES AUTHENTICATION
Route::middleware('auth')->group(function () {
    // Contact Page View
    Route::get('/contact', function () {
        return Inertia::render('Contact');
    })->name('contact');

    // Contact Form Submission
    Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
});

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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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

        // Privacy settings update
        Route::put('/profile/privacy', [ProfileController::class, 'updatePrivacy'])
            ->middleware('auth')
            ->name('profile.privacy.update');
    });

    // Grozs
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/add', [CartController::class, 'add'])->name('add');
        Route::patch('/item/{cartItem}', [CartController::class, 'updateQuantity'])->name('update');
        Route::delete('/item/{cartItem}', [CartController::class, 'remove'])->name('remove');
        Route::delete('/clear', [CartController::class, 'clear'])->name('clear');
        Route::get('/count', [CartController::class, 'count'])->name('count');
        Route::get('/data', [CartController::class, 'get'])->name('get');
    });

    // Checkout
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');

    // Pasūtījumi
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/{id}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/', [OrderController::class, 'store'])->name('orders.store');
        Route::put('/{id}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    });

    // Rēķinu lejupielāde
    Route::get('/orders/{id}/invoice', [InvoiceController::class, 'download'])->name('orders.invoice.download');
    Route::get('/orders/{id}/invoice/view', [InvoiceController::class, 'view'])->name('orders.invoice.view');

    // Atsauksmes
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::put('/reviews/{id}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    // Komentāri
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::put('/comments/{id}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{id}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // Like content (WEB route for session auth)
    Route::post('/content/{id}/like', [ContentController::class, 'toggleLike'])->name('content.like');
});

// ========== ADMIN ROUTES ==========

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Administrator Management (Super Admin only)
    Route::post('/administrators', [AdminDashboardController::class, 'storeAdministrator'])->name('administrators.store');
    Route::put('/administrators/{id}/permissions', [AdminDashboardController::class, 'updatePermissions'])->name('administrators.permissions');
    Route::delete('/administrators/{id}', [AdminDashboardController::class, 'destroyAdministrator'])->name('administrators.destroy');

    // Products
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [AdminProductController::class, 'index'])->name('index');
        Route::get('/create', [AdminProductController::class, 'create'])->name('create');
        Route::post('/', [AdminProductController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [AdminProductController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminProductController::class, 'update'])->name('update');
        Route::put('/{id}/toggle-status', [AdminProductController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/{id}', [AdminProductController::class, 'destroy'])->name('destroy');
    });

    // Categories
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [AdminCategoryController::class, 'index'])->name('index');
        Route::post('/', [AdminCategoryController::class, 'store'])->name('store');
        Route::put('/{id}', [AdminCategoryController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdminCategoryController::class, 'destroy'])->name('destroy');
    });

    // Orders
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [AdminOrderController::class, 'index'])->name('index');
        Route::get('/{order}', [AdminOrderController::class, 'show'])->name('show');
        Route::put('/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('status');
        Route::get('/{order}/invoice/pdf', [AdminOrderController::class, 'downloadInvoicePdf'])->name('invoice.pdf');
        Route::get('/{order}/invoice/print', [AdminOrderController::class, 'printInvoice'])->name('invoice.print');
    });

    // Content
    Route::prefix('content')->name('content.')->group(function () {
        Route::get('/', [AdminContentController::class, 'index'])->name('index');
        Route::get('/create', [AdminContentController::class, 'create'])->name('create');
        Route::post('/', [AdminContentController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [AdminContentController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminContentController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdminContentController::class, 'destroy'])->name('destroy');
    });

    // Reviews
    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/', [AdminReviewController::class, 'index'])->name('index');
        Route::put('/{id}/approve', [AdminReviewController::class, 'approve'])->name('approve');
        Route::put('/{id}/reject', [AdminReviewController::class, 'reject'])->name('reject');
    });

    // Comments
    Route::prefix('comments')->name('comments.')->group(function () {
        Route::get('/', [AdminCommentController::class, 'index'])->name('index');
        Route::put('/{id}/approve', [AdminCommentController::class, 'approve'])->name('approve');
        Route::put('/{id}/reject', [AdminCommentController::class, 'reject'])->name('reject');
    });

    // Contact Messages
    Route::prefix('contacts')->name('contacts.')->group(function () {
        Route::get('/', [AdminContactController::class, 'index'])->name('index');
        Route::get('/{id}', [AdminContactController::class, 'show'])->name('show');
        Route::put('/{id}/read', [AdminContactController::class, 'markAsRead'])->name('read');
        Route::put('/{id}/reply', [AdminContactController::class, 'reply'])->name('reply');
        Route::delete('/{id}', [AdminContactController::class, 'destroy'])->name('destroy');
    });

    // Users
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [AdminUserController::class, 'index'])->name('index');
        Route::get('/{id}', [AdminUserController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [AdminUserController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminUserController::class, 'update'])->name('update');
        Route::put('/{id}/toggle-active', [AdminUserController::class, 'toggleActive'])->name('toggle-active');
        Route::put('/{id}/reset-password', [AdminUserController::class, 'resetPassword'])->name('reset-password');
        Route::delete('/{id}', [AdminUserController::class, 'destroy'])->name('destroy');
    });

});

// ========== AUTH AND ADMIN ROUTES (BREEZE) ==========
require __DIR__.'/auth.php';
