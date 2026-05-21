<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PerkaraController;
use App\Http\Controllers\PerkaraRecapController;

Route::redirect('/', '/dashboard');

// Dashboard routes
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::post('/upload', [DashboardController::class, 'upload'])->name('upload');
Route::post('/clear', [DashboardController::class, 'clear'])->name('clear');
Route::get('/sheet/{name}', [DashboardController::class, 'getSheet'])->name('sheet.get');

// Legacy routes
Route::resource('perkaras', PerkaraController::class);
Route::get('perkaras-recap', [PerkaraRecapController::class, 'index'])->name('perkaras.recap');
