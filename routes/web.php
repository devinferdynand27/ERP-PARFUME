<?php

use App\Http\Controllers\AromaController;
use App\Http\Controllers\PenerimaanBarangController;
use App\Http\Controllers\PermintaanBarangController;
use App\Http\Controllers\PesananPembelianController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('master/aroma')->name('aroma.')->group(function () {
    Route::get('/', [AromaController::class, 'index'])->name('index');
    Route::get('/data', [AromaController::class, 'data'])->name('data');
    Route::post('/', [AromaController::class, 'store'])->name('store');
    Route::put('/{arid}', [AromaController::class, 'update'])->name('update');
    Route::patch('/{arid}/toggle-aktif', [AromaController::class, 'toggleAktif'])->name('toggle-aktif');
});

Route::prefix('master/supplier')->name('supplier.')->group(function () {
    Route::get('/', [SupplierController::class, 'index'])->name('index');
    Route::get('/data', [SupplierController::class, 'data'])->name('data');
    Route::post('/', [SupplierController::class, 'store'])->name('store');
    Route::put('/{spid}', [SupplierController::class, 'update'])->name('update');
    Route::patch('/{spid}/toggle-aktif', [SupplierController::class, 'toggleAktif'])->name('toggle-aktif');
});

Route::prefix('master/satuan')->name('satuan.')->group(function () {
    Route::get('/', [SatuanController::class, 'index'])->name('index');
    Route::get('/data', [SatuanController::class, 'data'])->name('data');
    Route::post('/', [SatuanController::class, 'store'])->name('store');
    Route::put('/{stid}', [SatuanController::class, 'update'])->name('update');
    Route::patch('/{stid}/toggle-aktif', [SatuanController::class, 'toggleAktif'])->name('toggle-aktif');
});

Route::prefix('master/produk')->name('produk.')->group(function () {
    Route::get('/', [ProdukController::class, 'index'])->name('index');
    Route::get('/data', [ProdukController::class, 'data'])->name('data');
    Route::get('/form-options', [ProdukController::class, 'formOptions'])->name('form-options');
    Route::post('/', [ProdukController::class, 'store'])->name('store');
    Route::put('/{prid}', [ProdukController::class, 'update'])->name('update');
    Route::patch('/{prid}/toggle-aktif', [ProdukController::class, 'toggleAktif'])->name('toggle-aktif');
});

Route::prefix('transaksi/permintaan-barang')->name('permintaan-barang.')->group(function () {
    Route::get('/', [PermintaanBarangController::class, 'index'])->name('index');
    Route::get('/data', [PermintaanBarangController::class, 'data'])->name('data');
    Route::get('/form-options', [PermintaanBarangController::class, 'formOptions'])->name('form-options');
    Route::get('/create', [PermintaanBarangController::class, 'create'])->name('create');
    Route::get('/{pbid}/edit', [PermintaanBarangController::class, 'edit'])->name('edit');
    Route::get('/{pbid}', [PermintaanBarangController::class, 'show'])->name('show');
    Route::post('/', [PermintaanBarangController::class, 'store'])->name('store');
    Route::put('/{pbid}', [PermintaanBarangController::class, 'update'])->name('update');
    Route::patch('/{pbid}/status', [PermintaanBarangController::class, 'updateStatus'])->name('status');
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
