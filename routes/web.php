<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\LaporanController;

Route::get('/', function () {
    return redirect('/native-php/index.php');
});

// Route Data Absensi
Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi.index');
Route::post('/absensi/import', [AbsensiController::class, 'importExcel'])->name('absensi.import');
Route::post('/absensi/store', [AbsensiController::class, 'storeManual'])->name('absensi.store');

// Route Laporan
Route::get('/laporan/uang-makan', [LaporanController::class, 'uangMakan'])->name('laporan.uang-makan');
Route::get('/laporan/lembur', [LaporanController::class, 'lembur'])->name('laporan.lembur');
