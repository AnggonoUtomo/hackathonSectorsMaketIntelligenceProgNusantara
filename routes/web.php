<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::get('temukan-saham', function () {
        return Inertia::render('nusalens/placeholder', ['section' => 'discover']);
    })->name('discover');

    Route::get('perusahaan', function () {
        return Inertia::render('nusalens/placeholder', ['section' => 'companies']);
    })->name('companies');

    Route::get('bandingkan', function () {
        return Inertia::render('nusalens/placeholder', ['section' => 'compare']);
    })->name('compare');

    Route::get('jelaskan-nilai', function () {
        return Inertia::render('nusalens/placeholder', ['section' => 'research']);
    })->name('research');

    Route::get('kandidat-menarik', function () {
        return Inertia::render('nusalens/placeholder', ['section' => 'candidates']);
    })->name('candidates');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
