<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SotkMasterController;
use App\Http\Controllers\Admin\SotkUploadController;

Route::get('/', function () {
    return redirect()->route('admin.sotk.index');
});

// ─── Admin: Data Master SOTK ──────────────────────────────────────────────────
Route::prefix('admin/sotk')->name('admin.sotk.')->group(function () {

    // Daftar Data Master SOTK
    Route::get('/',                       [SotkMasterController::class, 'index'])->name('index');

    // Upload SOTK
    Route::get('/upload',                 [SotkUploadController::class, 'form'])->name('upload.form');
    Route::post('/upload/preview',        [SotkUploadController::class, 'preview'])->name('upload.preview');
    Route::get('/upload/preview/show',    [SotkUploadController::class, 'previewShow'])->name('upload.preview.show');
    Route::post('/upload/store',          [SotkUploadController::class, 'store'])->name('upload.store');
    Route::get('/upload/cancel',          [SotkUploadController::class, 'cancel'])->name('upload.cancel');

    // Hapus periode
    Route::delete('/{period}',            [SotkMasterController::class, 'destroy'])->name('destroy');

    // Export ke Excel
    Route::get('/export/{period}',        [SotkMasterController::class, 'export'])->name('export');
});

// ─── Admin: Org Chart ─────────────────────────────────────────────────────────
Route::prefix('admin/orgchart')->name('admin.orgchart.')->group(function () {
    Route::get('/',                       [\App\Http\Controllers\Admin\SotkOrgChartController::class, 'index'])->name('index');
    Route::get('/show',                   [\App\Http\Controllers\Admin\SotkOrgChartController::class, 'show'])->name('show');
});
