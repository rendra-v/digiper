<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PerkaraController;
use App\Http\Controllers\PerkaraRecapController;

Route::redirect('/', '/perkaras');

Route::resource('perkaras', PerkaraController::class);
Route::get('perkaras-recap', [PerkaraRecapController::class, 'index'])->name('perkaras.recap');
