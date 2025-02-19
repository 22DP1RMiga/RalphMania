<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// For Home page
Route::get('/', function () {
    return Inertia::render('HomeView', [
        'background' => 'mania/public/img/Coder_RoltonsLV.png',
        'title' => 'HOME | RalphMania'
    ]);
})->name('home');

// For About page
Route::get('/about', function () {
    return Inertia::render('AboutView', [
        'background' => 'Hostage_Adventure.png',
        'overlay' => true,
        'title' => 'ABOUT | RalphMania'
    ]);
})->name('about');

// For Contacts page
Route::get('/contacts', function () {
    return Inertia::render('ContactsView', [
        'background' => 'Hostage_Adventure.png',
        'overlay' => true,
        'title' => 'CONTACTS | RalphMania'
    ]);
})->name('contacts');

// For Shop page
Route::get('/shop', function () {
    return Inertia::render('ShopView', [
        'background' => 'white',
        'title' => 'SHOP | RalphMania'
    ]);
})->name('shop');
