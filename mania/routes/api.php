<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\VideoController;

// API Routes: Login/Register
Route::get('/register', [RegisterController::class, 'create']);
Route::post('/register', [RegisterController::class, 'store'])->name('register');
Route::get('/login', [LoginController::class, 'create']);
Route::post('/login', [LoginController::class, 'store']);

// API Routes: Videos
//Route::get('/videos', [VideoController::class, 'index']);
//Route::get('/videos', function () {
//    return \App\Models\Video::all();
//});

// API Routes: Reviews
Route::middleware('auth:sanctum')->post('/reviews', [ReviewController::class, 'store']);

// API Routes: User Information
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

