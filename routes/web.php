<?php

use App\Http\Controllers\AromaController;
use App\Http\Controllers\KualitasBibitController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UkuranBotolController;
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

Route::prefix('master/ukuran-botol')->name('ukuran-botol.')->group(function () {
    Route::get('/', [UkuranBotolController::class, 'index'])->name('index');
    Route::get('/data', [UkuranBotolController::class, 'data'])->name('data');
    Route::post('/', [UkuranBotolController::class, 'store'])->name('store');
    Route::put('/{ubid}', [UkuranBotolController::class, 'update'])->name('update');
    Route::patch('/{ubid}/toggle-aktif', [UkuranBotolController::class, 'toggleAktif'])->name('toggle-aktif');
});

Route::prefix('master/kualitas-bibit')->name('kualitas-bibit.')->group(function () {
    Route::get('/', [KualitasBibitController::class, 'index'])->name('index');
    Route::get('/data', [KualitasBibitController::class, 'data'])->name('data');
    Route::post('/', [KualitasBibitController::class, 'store'])->name('store');
    Route::put('/{kbid}', [KualitasBibitController::class, 'update'])->name('update');
    Route::patch('/{kbid}/toggle-aktif', [KualitasBibitController::class, 'toggleAktif'])->name('toggle-aktif');
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
