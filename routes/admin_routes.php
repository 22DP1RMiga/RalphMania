<?php

/**
 * Admin Routes - Pievienojas web.php failam
 */

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ContactController;

// ========== ADMIN ROUTES ==========

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {

    // Admin Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // Administrator Management (Super Admin only)
    Route::post('/administrators', [AdminDashboardController::class, 'storeAdministrator'])->name('admin.administrators.store');
    Route::put('/administrators/{id}/permissions', [AdminDashboardController::class, 'updatePermissions'])->name('admin.administrators.permissions');
    Route::delete('/administrators/{id}', [AdminDashboardController::class, 'destroyAdministrator'])->name('admin.administrators.destroy');

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
            return \Inertia\Inertia::render('Admin/Users/Index');
        })->name('admin.users.index');

        Route::put('/{id}/toggle-active', function ($id) {
            // TODO: Implement user activation toggle
        })->name('admin.users.toggle');
    });
});
