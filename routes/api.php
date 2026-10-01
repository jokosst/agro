<?php

use App\Http\Controllers\Api\AbsensiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LaporanHarianController;
use App\Http\Controllers\Api\LaporanMasalahController;
use App\Http\Controllers\Api\MasterApiController;
use App\Http\Controllers\Api\MonitoringController;
use App\Http\Controllers\Api\PemeriksaanController;
use App\Http\Controllers\Api\TugasController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Auth & Worker Dashboard
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/profile/update', [AuthController::class, 'updateProfile']);
    Route::post('/profile/password', [AuthController::class, 'updatePassword']);

    // Master Data (Sesuai dengan Data Master Admin)
    Route::get('/master/lahan', [MasterApiController::class, 'lahan']);
    Route::get('/master/data-dukung', [MasterApiController::class, 'dataDukung']);
    Route::get('/master/pekerja', [MasterApiController::class, 'pekerja']);

    // Notifikasi & Pengingat Aktif (Icon Lonceng)
    Route::get('/notifikasi', [MasterApiController::class, 'notifikasi']);

    // Absensi
    Route::post('/absen/masuk', [AbsensiController::class, 'absenMasuk']);
    Route::post('/absen/pulang', [AbsensiController::class, 'absenPulang']);
    Route::get('/absen/today', [AbsensiController::class, 'today']);

    // Tugas Hari Ini (Checklist)
    Route::get('/tugas', [TugasController::class, 'index']);
    Route::post('/tugas/toggle/{id}', [TugasController::class, 'toggle']);

    // Pemeriksaan Tanaman (Daun, Batang, Bunga, Buah)
    Route::post('/pemeriksaan', [PemeriksaanController::class, 'store']);
    Route::get('/pemeriksaan/today', [PemeriksaanController::class, 'today']);

    // Laporan Masalah (Hama/Penyakit/Gulma per Blok)
    Route::get('/laporan-masalah', [LaporanMasalahController::class, 'index']);
    Route::post('/laporan-masalah', [LaporanMasalahController::class, 'store']);

    // Laporan Harian (Rangkuman & Foto Sebelum/Sesudah)
    Route::get('/laporan-harian/today', [LaporanHarianController::class, 'today']);
    Route::post('/laporan-harian', [LaporanHarianController::class, 'store']);

    // Monitoring Kebun & Rekap
    Route::get('/monitoring-kebun', [MonitoringController::class, 'kebunInfo']);
    Route::get('/rekap-laporan', [MonitoringController::class, 'rekap']);
});
