<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\LiburController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\LemburController;

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// Protected Routes
Route::middleware([\App\Http\Middleware\CheckAuth::class])->group(function () {
    
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Master Data Pegawai
    Route::get('/pegawai', [PegawaiController::class, 'index'])->name('pegawai.index');
    Route::post('/pegawai', [PegawaiController::class, 'store'])->name('pegawai.store');
    Route::put('/pegawai/{id}', [PegawaiController::class, 'update'])->name('pegawai.update');
    Route::delete('/pegawai/{id}', [PegawaiController::class, 'destroy'])->name('pegawai.destroy');

    // Master Data Shift
    Route::get('/shift', [ShiftController::class, 'index'])->name('shift.index');
    Route::post('/shift', [ShiftController::class, 'store'])->name('shift.store');
    Route::put('/shift/{id}', [ShiftController::class, 'update'])->name('shift.update');
    Route::delete('/shift/{id}', [ShiftController::class, 'destroy'])->name('shift.destroy');

    // Master Data Libur
    Route::get('/libur', [LiburController::class, 'index'])->name('libur.index');
    Route::post('/libur', [LiburController::class, 'store'])->name('libur.store');
    Route::put('/libur/{id}', [LiburController::class, 'update'])->name('libur.update');
    Route::delete('/libur/{id}', [LiburController::class, 'destroy'])->name('libur.destroy');

    // Jadwal Kerja
    Route::get('/jadwal', [JadwalController::class, 'index'])->name('jadwal.index');
    Route::post('/jadwal/set', [JadwalController::class, 'setJadwal'])->name('jadwal.set');
    Route::post('/jadwal/bulk', [JadwalController::class, 'bulkSet'])->name('jadwal.bulk');

    // Route Data Absensi
    Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi.index');
    Route::post('/absensi/import', [AbsensiController::class, 'importExcel'])->name('absensi.import');
    Route::post('/absensi/store', [AbsensiController::class, 'storeManual'])->name('absensi.store');
    Route::delete('/absensi/{id}', [AbsensiController::class, 'destroy'])->name('absensi.destroy');

    // Route Data Lembur
    Route::get('/lembur/register', [LemburController::class, 'register'])->name('lembur.register');
    Route::post('/lembur/register', [LemburController::class, 'store'])->name('lembur.store');
    Route::delete('/lembur/{id}', [LemburController::class, 'destroy'])->name('lembur.destroy');

    // Route Laporan
    Route::get('/laporan/uang-makan', [LaporanController::class, 'uangMakan'])->name('laporan.uang-makan');
    Route::get('/laporan/lembur', [LaporanController::class, 'lembur'])->name('laporan.lembur');
    Route::get('/laporan/lembur/detail', [LaporanController::class, 'lemburDetail'])->name('laporan.lembur.detail');

});
