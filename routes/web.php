<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AromaController;
use App\Http\Controllers\KualitasBibitController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UkuranBotolController;
use Illuminate\Support\Facades\Route;

// Rute untuk Tamu (Guest) - Belum Login
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Rute Terotentikasi - Harus Login
Route::middleware('auth')->group(function () {
    // Proses Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Halaman Dashboard Utama
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Rute Khusus Admin - Master Data (RBAC)
    Route::middleware('role:admin')->group(function () {
        // Master Aroma
        Route::prefix('master/aroma')->name('aroma.')->group(function () {
            Route::get('/', [AromaController::class, 'index'])->name('index');
            Route::get('/data', [AromaController::class, 'data'])->name('data');
            Route::post('/', [AromaController::class, 'store'])->name('store');
            Route::put('/{arid}', [AromaController::class, 'update'])->name('update');
            Route::patch('/{arid}/toggle-aktif', [AromaController::class, 'toggleAktif'])->name('toggle-aktif');
        });

        // Master Ukuran Botol
        Route::prefix('master/ukuran-botol')->name('ukuran-botol.')->group(function () {
            Route::get('/', [UkuranBotolController::class, 'index'])->name('index');
            Route::get('/data', [UkuranBotolController::class, 'data'])->name('data');
            Route::post('/', [UkuranBotolController::class, 'store'])->name('store');
            Route::put('/{ubid}', [UkuranBotolController::class, 'update'])->name('update');
            Route::patch('/{ubid}/toggle-aktif', [UkuranBotolController::class, 'toggleAktif'])->name('toggle-aktif');
        });

        // Master Kualitas Bibit
        Route::prefix('master/kualitas-bibit')->name('kualitas-bibit.')->group(function () {
            Route::get('/', [KualitasBibitController::class, 'index'])->name('index');
            Route::get('/data', [KualitasBibitController::class, 'data'])->name('data');
            Route::post('/', [KualitasBibitController::class, 'store'])->name('store');
            Route::put('/{kbid}', [KualitasBibitController::class, 'update'])->name('update');
            Route::patch('/{kbid}/toggle-aktif', [KualitasBibitController::class, 'toggleAktif'])->name('toggle-aktif');
        });

        // Master Supplier
        Route::prefix('master/supplier')->name('supplier.')->group(function () {
            Route::get('/', [SupplierController::class, 'index'])->name('index');
            Route::get('/data', [SupplierController::class, 'data'])->name('data');
            Route::post('/', [SupplierController::class, 'store'])->name('store');
            Route::put('/{spid}', [SupplierController::class, 'update'])->name('update');
            Route::patch('/{spid}/toggle-aktif', [SupplierController::class, 'toggleAktif'])->name('toggle-aktif');
        });

        // Master Satuan
        Route::prefix('master/satuan')->name('satuan.')->group(function () {
            Route::get('/', [SatuanController::class, 'index'])->name('index');
            Route::get('/data', [SatuanController::class, 'data'])->name('data');
            Route::post('/', [SatuanController::class, 'store'])->name('store');
            Route::put('/{stid}', [SatuanController::class, 'update'])->name('update');
            Route::patch('/{stid}/toggle-aktif', [SatuanController::class, 'toggleAktif'])->name('toggle-aktif');
        });

        // Master Produk
        Route::prefix('master/produk')->name('produk.')->group(function () {
            Route::get('/', [ProdukController::class, 'index'])->name('index');
            Route::get('/data', [ProdukController::class, 'data'])->name('data');
            Route::get('/form-options', [ProdukController::class, 'formOptions'])->name('form-options');
            Route::post('/', [ProdukController::class, 'store'])->name('store');
            Route::put('/{prid}', [ProdukController::class, 'update'])->name('update');
            Route::patch('/{prid}/toggle-aktif', [ProdukController::class, 'toggleAktif'])->name('toggle-aktif');
        });
    });
});
