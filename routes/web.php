<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DocumentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// 1. HARUS LOGIN (Dashboard, Tambah Dokumen, & Profile)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DocumentController::class, 'index'])->name('dashboard');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// 2. BISA DIAKSES TAMU / TANPA LOGIN
Route::get('/editor/{id}', [DocumentController::class, 'show'])->name('editor.show');

// 3. HANYA USER LOGIN YANG BISA SAVE DOKUMEN
Route::middleware(['auth'])->put('/documents/{id}', [DocumentController::class, 'update'])->name('documents.update');

require __DIR__.'/auth.php';