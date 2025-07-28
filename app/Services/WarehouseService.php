<?php

namespace App\Services;

use App\Models\StockBarang;
use App\Models\History;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WarehouseService
{
    /**
     * Update stock for barang
     */
    public function updateStock($idBarang, $qty, $type = 'masuk')
    {
        $stock = StockBarang::where('id_barang', $idBarang)->first();

        if ($stock) {
            if ($type === 'masuk') {
                $stock->qty += $qty;
            } else {
                $stock->qty -= $qty;
            }
            $stock->updated_by = Auth::id();
            $stock->save();
        } else {
            // Create new stock record
            StockBarang::create([
                'id_barang' => $idBarang,
                'qty' => $type === 'masuk' ? $qty : 0,
                'created_by' => Auth::id(),
            ]);
        }

        return $stock;
    }

    /**
     * Check if stock is sufficient
     */
    public function checkStockAvailability($idBarang, $requiredQty)
    {
        $stock = StockBarang::where('id_barang', $idBarang)->first();

        if (!$stock) {
            return ['available' => false, 'current_stock' => 0];
        }

        return [
            'available' => $stock->qty >= $requiredQty,
            'current_stock' => $stock->qty
        ];
    }

    /**
     * Create history record
     */
    public function createHistory($data)
    {
        return History::create(array_merge($data, [
            'created_by' => Auth::id()
        ]));
    }

    /**
     * Get stock summary
     */
    public function getStockSummary()
    {
        return [
            'total_items' => StockBarang::count(),
            'total_stock' => StockBarang::sum('qty'),
            'low_stock_items' => StockBarang::where('qty', '<=', 10)->count(),
            'out_of_stock_items' => StockBarang::where('qty', '=', 0)->count(),
            'last_updated' => StockBarang::latest('updated_at')->first()?->updated_at
        ];
    }

    /**
     * Get low stock items
     */
    public function getLowStockItems($threshold = 10)
    {
        return StockBarang::with(['barang'])
            ->where('qty', '<=', $threshold)
            ->orderBy('qty', 'asc')
            ->get();
    }

    /**
     * Process barang masuk with stock update and history
     */
    public function processBarangMasuk($data)
    {
        return DB::transaction(function() use ($data) {
            // Create barang masuk record
            $barangMasuk = \App\Models\BarangMasuk::create(array_merge($data, [
                'created_by' => Auth::id()
            ]));

            // Update stock
            $this->updateStock($data['id_barang'], $data['qty'], 'masuk');

            // Create history
            $this->createHistory([
                'id_barang' => $data['id_barang'],
                'qty' => $data['qty'],
                'id_barang_masuk' => $barangMasuk->id,
            ]);

            return $barangMasuk;
        });
    }

    /**
     * Process pengeluaran barang with stock update and history
     */
    public function processPengeluaranBarang($data)
    {
        return DB::transaction(function() use ($data) {
            // Check stock availability
            $stockCheck = $this->checkStockAvailability($data['id_barang'], $data['qty']);

            if (!$stockCheck['available']) {
                throw new \Exception('Stock tidak mencukupi. Stock tersedia: ' . $stockCheck['current_stock']);
            }

            // Create pengeluaran barang record
            $pengeluaranBarang = \App\Models\PengeluaranBarang::create(array_merge($data, [
                'created_by' => Auth::id()
            ]));

            // Update stock
            $this->updateStock($data['id_barang'], $data['qty'], 'keluar');

            // Create history
            $this->createHistory([
                'id_barang' => $data['id_barang'],
                'qty' => $data['qty'],
                'id_barang_keluar' => $pengeluaranBarang->id,
            ]);

            return $pengeluaranBarang;
        });
    }
}
