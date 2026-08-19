<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AromaController;
use App\Http\Controllers\PenerimaanBarangController;
use App\Http\Controllers\PermintaanBarangController;
use App\Http\Controllers\PesananPembelianController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\SupplierController;
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
    });

    Route::prefix('transaksi/permintaan-barang')->name('permintaan-barang.')->group(function () {
        Route::get('/', [PermintaanBarangController::class, 'index'])->name('index');
        Route::get('/data', [PermintaanBarangController::class, 'data'])->name('data');
        Route::get('/form-options', [PermintaanBarangController::class, 'formOptions'])->name('form-options');
        Route::get('/aroma-data', [PermintaanBarangController::class, 'aromaData'])->name('aroma-data');
        Route::get('/create', [PermintaanBarangController::class, 'create'])->name('create');
        Route::get('/{pbid}/edit', [PermintaanBarangController::class, 'edit'])->name('edit');
        Route::get('/{pbid}/print', [PermintaanBarangController::class, 'print'])->name('print');
        Route::get('/{pbid}', [PermintaanBarangController::class, 'show'])->name('show');
        Route::post('/', [PermintaanBarangController::class, 'store'])->name('store');
        Route::put('/{pbid}', [PermintaanBarangController::class, 'update'])->name('update');
        Route::patch('/{pbid}/status', [PermintaanBarangController::class, 'updateStatus'])->name('status');
        Route::delete('/{pbid}', [PermintaanBarangController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('transaksi/pesanan-pembelian')->name('pesanan-pembelian.')->group(function () {
        Route::get('/', [PesananPembelianController::class, 'index'])->name('index');
        Route::get('/data', [PesananPembelianController::class, 'data'])->name('data');
        Route::get('/form-options', [PesananPembelianController::class, 'formOptions'])->name('form-options');
        Route::get('/create', [PesananPembelianController::class, 'create'])->name('create');
        Route::get('/dari-permintaan/{pbid}', [PesananPembelianController::class, 'dariPermintaan'])->name('dari-permintaan');
        Route::get('/{ppid}', [PesananPembelianController::class, 'show'])->name('show');
        Route::post('/', [PesananPembelianController::class, 'store'])->name('store');
        Route::patch('/{ppid}/status', [PesananPembelianController::class, 'updateStatus'])->name('status');
    });

    Route::prefix('transaksi/penerimaan-barang')->name('penerimaan-barang.')->group(function () {
        Route::get('/', [PenerimaanBarangController::class, 'index'])->name('index');
        Route::get('/data', [PenerimaanBarangController::class, 'data'])->name('data');
        Route::get('/form-options', [PenerimaanBarangController::class, 'formOptions'])->name('form-options');
        Route::get('/create', [PenerimaanBarangController::class, 'create'])->name('create');
        Route::get('/{pnid}', [PenerimaanBarangController::class, 'show'])->name('show');
        Route::post('/', [PenerimaanBarangController::class, 'store'])->name('store');
    });
});
