<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\BarangKategoriController;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\PengeluaranBarangController;
use App\Http\Controllers\StockBarangController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ApprovalController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Barang routes
Route::apiResource('barang', BarangController::class);
Route::get('barang-with-stock', [BarangController::class, 'withStock']);

// Barang Kategori routes
Route::apiResource('barang-kategori', BarangKategoriController::class);

// Barang Masuk routes
Route::apiResource('barang-masuk', BarangMasukController::class);
Route::patch('barang-masuk/{barangMasuk}/approve', [BarangMasukController::class, 'approve']);

// Pengeluaran Barang routes
Route::apiResource('pengeluaran-barang', PengeluaranBarangController::class);
Route::patch('pengeluaran-barang/{pengeluaranBarang}/approve', [PengeluaranBarangController::class, 'approve']);

// Stock Barang routes
Route::get('stock-barang', [StockBarangController::class, 'index']);
Route::get('stock-barang/{stockBarang}', [StockBarangController::class, 'show']);
Route::get('stock-barang/barang/{barangId}', [StockBarangController::class, 'getByBarang']);
Route::get('stock-barang/low-stock/{threshold?}', [StockBarangController::class, 'lowStock']);
Route::get('stock-barang-summary', [StockBarangController::class, 'summary']);
Route::patch('stock-barang/{stockBarang}/adjust', [StockBarangController::class, 'adjust']);

// History routes
Route::get('history', [HistoryController::class, 'index']);
Route::get('history/{history}', [HistoryController::class, 'show']);
Route::get('history/barang/{barangId}', [HistoryController::class, 'getByBarang']);
Route::get('history-stock-movement', [HistoryController::class, 'stockMovement']);
Route::get('history-daily-report', [HistoryController::class, 'dailyReport']);

// Approval routes
Route::apiResource('approvals', ApprovalController::class)->only(['index', 'store', 'show']);
Route::get('approvals/type/{type}', [ApprovalController::class, 'getByType']);
Route::get('approvals-pending', [ApprovalController::class, 'pending']);
