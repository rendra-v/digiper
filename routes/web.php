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
Route::get('/rekap-keseluruhan', [DashboardController::class, 'rekapKeseluruhan'])->name('rekap-keseluruhan');
Route::get('/rekap-keseluruhan/print', [DashboardController::class, 'rekapKeseluruhanPrint'])->name('rekap-keseluruhan.print');
Route::get('/rekap-keseluruhan-2', [DashboardController::class, 'rekapKeseluruhan2'])->name('rekap-keseluruhan-2');
Route::get('/rekap-keseluruhan-2/print', [DashboardController::class, 'rekapKeseluruhan2Print'])->name('rekap-keseluruhan-2.print');
Route::get('/rekap-keseluruhan-3', [DashboardController::class, 'rekapKeseluruhan3'])->name('rekap-keseluruhan-3');
Route::get('/rekap-keseluruhan-3/print', [DashboardController::class, 'rekapKeseluruhan3Print'])->name('rekap-keseluruhan-3.print');
Route::get('/honorarium', [DashboardController::class, 'honorarium'])->name('honorarium');
Route::get('/honorarium/print', [DashboardController::class, 'honorariumPrint'])->name('honorarium.print');
Route::get('/honorarium/debug', [DashboardController::class, 'honorariumDebug'])->name('honorarium.debug');


// Legacy routes
Route::resource('perkaras', PerkaraController::class);
Route::get('perkaras-recap', [PerkaraRecapController::class, 'index'])->name('perkaras.recap');

// Dev helper: re-inject latest uploaded file into current session
Route::get('/dev/inject-file', function () {
    $file = App\Models\ExcelFile::latest()->first();
    if (!$file) {
        return 'No file found.';
    }
    $fullPath = storage_path('app/' . $file->file_path);
    $originalName = $file->original_name ?: basename($file->file_path);
    session([
        'excel_file_path' => $fullPath,
        'excel_file_name' => $originalName,
    ]);
    return redirect('/honorarium');
});

