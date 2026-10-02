<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\WebAuthController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard (if authenticated) or login
Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

// Authentication Web Routes
Route::get('/login', [WebAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [WebAuthController::class, 'login'])->name('login.post');
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

// Protected Admin Panel Routes
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {

    // 1. Dashboard Utama
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    // 2. Monitoring Kebun (Peta Google Maps / Leaflet)
    Route::get('/monitoring-peta', [AdminController::class, 'monitoringPeta'])->name('monitoring_peta');

    // 3. Rekap Absensi
    Route::get('/absensi', [AdminController::class, 'absensi'])->name('absensi');
    Route::put('/absensi/{id}', [AdminController::class, 'updateAbsensi'])->name('absensi.update')->middleware('admin');
    Route::delete('/absensi/{id}', [AdminController::class, 'destroyAbsensi'])->name('absensi.destroy')->middleware('admin');

    // 4. Detail Laporan Pekerja (Harian Lengkap)
    Route::get('/pekerja/{id}/detail', [AdminController::class, 'detailPekerja'])->name('detail_pekerja');

    // 5. Laporan Masalah & Hama
    Route::get('/laporan-masalah', [AdminController::class, 'laporanMasalah'])->name('laporan_masalah');
    Route::post('/laporan-masalah/{id}/status', [AdminController::class, 'updateStatusMasalah'])->name('laporan_masalah.status')->middleware('admin');
    Route::put('/laporan-masalah/{id}', [AdminController::class, 'updateMasalah'])->name('laporan_masalah.update')->middleware('admin');
    Route::delete('/laporan-masalah/{id}', [AdminController::class, 'destroyMasalah'])->name('laporan_masalah.destroy')->middleware('admin');

    // 6. Rekap Laporan Harian, Mingguan, Bulanan
    Route::get('/laporan-harian', [AdminController::class, 'laporanHarian'])->name('laporan_harian');

    // ==========================================
    // DATA MASTER (CRUD DINAMIS - KHUSUS ADMIN)
    // ==========================================
    Route::prefix('master')->name('master.')->middleware('admin')->group(function () {

        // Master Lokasi / Lahan & Blok
        Route::get('/lahan', [MasterController::class, 'lahan'])->name('lahan');
        Route::put('/lahan/kebun/{id}', [MasterController::class, 'updateKebun'])->name('kebun.update');
        Route::post('/lahan/blok', [MasterController::class, 'storeBlok'])->name('blok.store');
        Route::put('/lahan/blok/{id}', [MasterController::class, 'updateBlok'])->name('blok.update');
        Route::delete('/lahan/blok/{id}', [MasterController::class, 'destroyBlok'])->name('blok.destroy');

        // Master Data Pekerja (Users)
        Route::get('/pekerja', [MasterController::class, 'pekerja'])->name('pekerja');
        Route::post('/pekerja', [MasterController::class, 'storePekerja'])->name('pekerja.store');
        Route::put('/pekerja/{id}', [MasterController::class, 'updatePekerja'])->name('pekerja.update');
        Route::post('/pekerja/{id}/reset-password', [MasterController::class, 'resetPasswordPekerja'])->name('pekerja.reset_password');
        Route::delete('/pekerja/{id}', [MasterController::class, 'destroyPekerja'])->name('pekerja.destroy');

        // Master Tugas Harian
        Route::get('/tugas-harian', [MasterController::class, 'tugasHarian'])->name('tugas_harian');
        Route::post('/tugas-harian', [MasterController::class, 'storeTugasHarian'])->name('tugas_harian.store');
        Route::put('/tugas-harian/{id}', [MasterController::class, 'updateTugasHarian'])->name('tugas_harian.update');
        Route::post('/tugas-harian/{id}/toggle', [MasterController::class, 'toggleTugasStatus'])->name('tugas_harian.toggle');
        Route::delete('/tugas-harian/{id}', [MasterController::class, 'destroyTugasHarian'])->name('tugas_harian.destroy');

        // Master Data Dukung (Hama & Penyakit)
        Route::get('/data-dukung', [MasterController::class, 'dataDukung'])->name('data_dukung');
        Route::post('/data-dukung', [MasterController::class, 'storeDataDukung'])->name('data_dukung.store');
        Route::put('/data-dukung/{id}', [MasterController::class, 'updateDataDukung'])->name('data_dukung.update');
        Route::delete('/data-dukung/{id}', [MasterController::class, 'destroyDataDukung'])->name('data_dukung.destroy');
    });

    // Backward compatibility redirects for old URLs
    Route::get('/pekerja', fn () => redirect()->route('admin.master.pekerja'))->middleware('admin');
    Route::get('/kebun', fn () => redirect()->route('admin.master.lahan'))->middleware('admin');
});
