<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Rute halaman depan (bawaan Laravel)
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Rute Dashboard bawaan Breeze (Bisa dibiarkan atau diubah)
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// KUNCI JAWABANNYA DI SINI: Bungkus rute editor ke dalam middleware auth
Route::middleware('auth')->group(function () {
    // Jalur untuk menampilkan halaman editor
    Route::get('/editor', [DocumentController::class, 'edit'])->name('editor.edit');
    
    // Jalur untuk memproses auto-save dari editor
    Route::post('/editor/save', [DocumentController::class, 'save'])->name('editor.save');
    
    // Rute profile bawaan Breeze
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';