<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PerkaraController;
use App\Http\Controllers\PerkaraRecapController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

// Dashboard routes
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::post('/upload', [DashboardController::class, 'upload'])->name('upload');
Route::post('/upload-with-period', [DashboardController::class, 'uploadWithPeriod'])->name('upload-with-period');
Route::get('/file/{id}', [DashboardController::class, 'viewFile'])->name('file.view');
Route::delete('/file/{id}', [DashboardController::class, 'deleteFile'])->name('file.delete');
Route::post('/file/{id}/rename-period', [DashboardController::class, 'renamePeriod'])->name('file.rename-period');
Route::post('/clear', [DashboardController::class, 'clear'])->name('clear');
Route::get('/sheet/{name}', [DashboardController::class, 'getSheet'])->name('sheet.get');
Route::get('/data-print', [DashboardController::class, 'dataPrint'])->name('data-print');
Route::get('/data-print/print', [DashboardController::class, 'printRekapKeseluruhan'])->name('data-print.print');
Route::get('/sheet-cek', [DashboardController::class, 'sheetCek'])->name('sheet-cek');

// Legacy routes
Route::resource('perkaras', PerkaraController::class);
Route::get('perkaras-recap', [PerkaraRecapController::class, 'index'])->name('perkaras.recap');
