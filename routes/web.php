<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\WebBarangController;
use App\Http\Controllers\Web\WebBarangMasukController;
use App\Http\Controllers\Web\WebPengeluaranBarangController;
use App\Http\Controllers\Web\WebStockController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Barang Management
Route::prefix('barang')->name('web.barang.')->group(function () {
    Route::get('/', [WebBarangController::class, 'index'])->name('index');
    Route::get('/create', [WebBarangController::class, 'create'])->name('create');
    Route::post('/', [WebBarangController::class, 'store'])->name('store');
    Route::get('/{barang}', [WebBarangController::class, 'show'])->name('show');
    Route::get('/{barang}/edit', [WebBarangController::class, 'edit'])->name('edit');
    Route::put('/{barang}', [WebBarangController::class, 'update'])->name('update');
    Route::delete('/{barang}', [WebBarangController::class, 'destroy'])->name('destroy');
});

// Barang Masuk Management
Route::prefix('barang-masuk')->name('web.barang-masuk.')->group(function () {
    Route::get('/', [WebBarangMasukController::class, 'index'])->name('index');
    Route::get('/create', [WebBarangMasukController::class, 'create'])->name('create');
    Route::post('/', [WebBarangMasukController::class, 'store'])->name('store');
    Route::get('/{barangMasuk}', [WebBarangMasukController::class, 'show'])->name('show');
    Route::get('/{barangMasuk}/edit', [WebBarangMasukController::class, 'edit'])->name('edit');
    Route::put('/{barangMasuk}', [WebBarangMasukController::class, 'update'])->name('update');
    Route::delete('/{barangMasuk}', [WebBarangMasukController::class, 'destroy'])->name('destroy');
    Route::patch('/{barangMasuk}/approve', [WebBarangMasukController::class, 'approve'])->name('approve');
});

// Pengeluaran Barang Management
Route::prefix('pengeluaran-barang')->name('web.pengeluaran-barang.')->group(function () {
    Route::get('/', [WebPengeluaranBarangController::class, 'index'])->name('index');
    Route::get('/create', [WebPengeluaranBarangController::class, 'create'])->name('create');
    Route::post('/', [WebPengeluaranBarangController::class, 'store'])->name('store');
    Route::get('/{pengeluaranBarang}', [WebPengeluaranBarangController::class, 'show'])->name('show');
    Route::get('/{pengeluaranBarang}/edit', [WebPengeluaranBarangController::class, 'edit'])->name('edit');
    Route::put('/{pengeluaranBarang}', [WebPengeluaranBarangController::class, 'update'])->name('update');
    Route::delete('/{pengeluaranBarang}', [WebPengeluaranBarangController::class, 'destroy'])->name('destroy');
    Route::patch('/{pengeluaranBarang}/approve', [WebPengeluaranBarangController::class, 'approve'])->name('approve');
});

// Stock Management
Route::prefix('stock')->name('web.stock.')->group(function () {
    Route::get('/', [WebStockController::class, 'index'])->name('index');
    Route::get('/{stock}', [WebStockController::class, 'show'])->name('show');
    Route::get('/{stock}/edit', [WebStockController::class, 'edit'])->name('edit');
    Route::put('/{stock}', [WebStockController::class, 'update'])->name('update');
    Route::get('/alerts/low-stock', [WebStockController::class, 'lowStock'])->name('low-stock');
    Route::get('/alerts/out-of-stock', [WebStockController::class, 'outOfStock'])->name('out-of-stock');
});
