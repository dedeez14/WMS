<?php

namespace App\Http\Controllers;

use App\Models\StockBarang;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockBarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stock = StockBarang::with(['barang', 'createdBy', 'updatedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $stock
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(StockBarang $stockBarang)
    {
        $stockBarang->load([
            'barang',
            'createdBy',
            'updatedBy'
        ]);

        return response()->json([
            'success' => true,
            'data' => $stockBarang
        ]);
    }

    /**
     * Get stock by barang ID
     */
    public function getByBarang($barangId)
    {
        $stock = StockBarang::with(['barang'])
            ->where('id_barang', $barangId)
            ->first();

        if (!$stock) {
            return response()->json([
                'success' => false,
                'message' => 'Stock untuk barang ini tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $stock
        ]);
    }

    /**
     * Get low stock items
     */
    public function lowStock($threshold = 10)
    {
        $lowStock = StockBarang::with(['barang'])
            ->where('qty', '<=', $threshold)
            ->orderBy('qty', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $lowStock
        ]);
    }

    /**
     * Get stock summary
     */
    public function summary()
    {
        $totalItems = StockBarang::count();
        $totalStock = StockBarang::sum('qty');
        $lowStockItems = StockBarang::where('qty', '<=', 10)->count();
        $outOfStockItems = StockBarang::where('qty', '=', 0)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_items' => $totalItems,
                'total_stock' => $totalStock,
                'low_stock_items' => $lowStockItems,
                'out_of_stock_items' => $outOfStockItems
            ]
        ]);
    }

    /**
     * Manual stock adjustment (for admin purposes)
     */
    public function adjust(Request $request, StockBarang $stockBarang)
    {
        $request->validate([
            'qty' => 'required|integer|min:0',
            'reason' => 'nullable|string'
        ]);

        $oldQty = $stockBarang->qty;
        $stockBarang->update([
            'qty' => $request->qty,
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Stock berhasil disesuaikan',
            'data' => [
                'old_qty' => $oldQty,
                'new_qty' => $request->qty,
                'difference' => $request->qty - $oldQty,
                'stock' => $stockBarang->load(['barang'])
            ]
        ]);
    }
}
