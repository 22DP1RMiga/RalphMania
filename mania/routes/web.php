<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('HomeView');
})->name('home');

Route::get('/about', function () {
    return Inertia::render('AboutView');
})->name('about');

Route::get('/contacts', function () {
    return Inertia::render('ContactsView');
})->name('contacts');

Route::get('/shop', function () {
    return Inertia::render('ShopView');
})->name('shop');
