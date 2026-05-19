<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DocumentController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
});

// DIUBAH: Sekarang dashboard menarik data secara resmi lewat Controller
Route::get('/dashboard', [DocumentController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/editor/{id}', [DocumentController::class, 'show'])->name('editor.show');
    Route::put('/editor/{id}', [DocumentController::class, 'update'])->name('editor.update');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
});

require __DIR__.'/auth.php';