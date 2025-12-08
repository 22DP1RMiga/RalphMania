<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ContactController;

// Public API routes
Route::prefix('v1')->group(function () {

    // Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/featured', [ProductController::class, 'featured']);
    Route::get('/products/{slug}', [ProductController::class, 'apiShow']); // ✅ Changed to apiShow

    // Categories
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{slug}', [CategoryController::class, 'show']);

    // Content
    Route::get('/content', [ContentController::class, 'index']);
    Route::get('/content/featured', [ContentController::class, 'featured']);
    Route::get('/content/{slug}', [ContentController::class, 'show']);
    Route::get('/content/type/{type}', [ContentController::class, 'byType']);

    // Reviews (public read)
    Route::get('/reviews/{type}/{id}', [ReviewController::class, 'byReviewable']);

    // Comments (public read)
    Route::get('/comments/content/{id}', [CommentController::class, 'byContent']);

    // Contact (public)
    Route::post('/contact', [ContactController::class, 'store']);
});

// Protected API routes
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {

    // Cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::put('/cart/{id}', [CartController::class, 'update']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);
    Route::delete('/cart/clear', [CartController::class, 'clear']);
    Route::post('/cart/sync', [CartController::class, 'sync']);
    Route::get('/cart/count', [CartController::class, 'count']);

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::put('/orders/{id}/cancel', [OrderController::class, 'cancel']);

    // Reviews (protected write)
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);

    // Comments (protected write)
    Route::post('/comments', [CommentController::class, 'store']);
    Route::put('/comments/{id}', [CommentController::class, 'update']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);
});

// Admin API routes
Route::middleware(['auth:sanctum', 'role:administrator'])->prefix('v1/admin')->group(function () {

    // Products
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // Categories
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    // Content
    Route::post('/content', [ContentController::class, 'store']);
    Route::put('/content/{id}', [ContentController::class, 'update']);
    Route::delete('/content/{id}', [ContentController::class, 'destroy']);

    // Orders
    Route::get('/orders/all', [OrderController::class, 'adminIndex']);
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);

    // Reviews moderation
    Route::put('/reviews/{id}/approve', [ReviewController::class, 'approve']);
    Route::put('/reviews/{id}/reject', [ReviewController::class, 'reject']);

    // Comments moderation
    Route::put('/comments/{id}/approve', [CommentController::class, 'approve']);
    Route::put('/comments/{id}/reject', [CommentController::class, 'reject']);

    // Contact messages
    Route::get('/contacts', [ContactController::class, 'index']);
    Route::put('/contacts/{id}/read', [ContactController::class, 'markAsRead']);
    Route::put('/contacts/{id}/reply', [ContactController::class, 'reply']);
});
