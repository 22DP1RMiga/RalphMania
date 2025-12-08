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

// Publiskie API maršruti
Route::prefix('v1')->group(function () {

    // Produkti
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/featured', [ProductController::class, 'featured']);
    Route::get('/products/{slug}', [ProductController::class, 'apiShow']);

    // Kategorijas
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{slug}', [CategoryController::class, 'show']);

    // Saturs
    Route::get('/content', [ContentController::class, 'apiIndex']);
    Route::get('/content/featured', [ContentController::class, 'featured']);
    Route::get('/content/{slug}', [ContentController::class, 'show']);
    Route::get('/content/type/{type}', [ContentController::class, 'byType']);

    // Atsauksmes (publiski salasāms)
    Route::get('/reviews/{type}/{id}', [ReviewController::class, 'byReviewable']);

    // Komentāri (publiski salasāms)
    Route::get('/comments/content/{id}', [CommentController::class, 'byContent']);

    // Kontakti (publisks)
    Route::post('/contact', [ContactController::class, 'store']);
});

// Aizsargāti API maršruti
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {

    // Grozs
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::put('/cart/{id}', [CartController::class, 'update']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);
    Route::delete('/cart/clear', [CartController::class, 'clear']);
    Route::post('/cart/sync', [CartController::class, 'sync']);
    Route::get('/cart/count', [CartController::class, 'count']);

    // Pasūtījumi
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::put('/orders/{id}/cancel', [OrderController::class, 'cancel']);

    // Atsauksmes (aizsargāta rakstīšana)
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);

    // Komentāri (aizsargāta rakstīšana)
    Route::post('/comments', [CommentController::class, 'store']);
    Route::put('/comments/{id}', [CommentController::class, 'update']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);
});

// Administratora API maršruti
Route::middleware(['auth:sanctum', 'role:administrator'])->prefix('v1/admin')->group(function () {

    // Produkti
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // Kategorijas
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    // Saturs
    Route::post('/content', [ContentController::class, 'store']);
    Route::put('/content/{id}', [ContentController::class, 'update']);
    Route::delete('/content/{id}', [ContentController::class, 'destroy']);

    // Pasūtījumi
    Route::get('/orders/all', [OrderController::class, 'adminIndex']);
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);

    // Atsauksmes moderācija
    Route::put('/reviews/{id}/approve', [ReviewController::class, 'approve']);
    Route::put('/reviews/{id}/reject', [ReviewController::class, 'reject']);

    // Komentāru moderācija
    Route::put('/comments/{id}/approve', [CommentController::class, 'approve']);
    Route::put('/comments/{id}/reject', [CommentController::class, 'reject']);

    // Kontaktu ziņas
    Route::get('/contacts', [ContactController::class, 'index']);
    Route::put('/contacts/{id}/read', [ContactController::class, 'markAsRead']);
    Route::put('/contacts/{id}/reply', [ContactController::class, 'reply']);
});
