<?php

namespace App\Http\Controllers;

use App\Models\PengeluaranBarang;
use App\Models\Barang;
use App\Models\StockBarang;
use App\Models\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PengeluaranBarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pengeluaranBarang = PengeluaranBarang::with(['barang', 'createdBy', 'approvedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pengeluaranBarang
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_barang' => 'required|exists:barang,id',
            'qty' => 'required|integer|min:1',
            'status' => 'nullable|string',
            'tujuan' => 'nullable|string',
        ]);

        // Check stock availability
        $stock = StockBarang::where('id_barang', $request->id_barang)->first();
        if (!$stock || $stock->qty < $request->qty) {
            return response()->json([
                'success' => false,
                'message' => 'Stock tidak mencukupi. Stock tersedia: ' . ($stock ? $stock->qty : 0)
            ], 422);
        }

        DB::beginTransaction();
        try {
            $pengeluaranBarang = PengeluaranBarang::create([
                'id_barang' => $request->id_barang,
                'qty' => $request->qty,
                'status' => $request->status,
                'tujuan' => $request->tujuan,
                'created_by' => Auth::id(),
            ]);

            // Update stock
            $this->updateStock($request->id_barang, $request->qty, 'keluar');

            // Create history record
            History::create([
                'id_barang' => $request->id_barang,
                'qty' => $request->qty,
                'id_barang_keluar' => $pengeluaranBarang->id,
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengeluaran barang berhasil ditambahkan',
                'data' => $pengeluaranBarang->load(['barang', 'createdBy'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan pengeluaran barang: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PengeluaranBarang $pengeluaranBarang)
    {
        $pengeluaranBarang->load([
            'barang',
            'createdBy',
            'updatedBy',
            'approvedBy',
            'history'
        ]);

        return response()->json([
            'success' => true,
            'data' => $pengeluaranBarang
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PengeluaranBarang $pengeluaranBarang)
    {
        $request->validate([
            'id_barang' => 'required|exists:barang,id',
            'qty' => 'required|integer|min:1',
            'status' => 'nullable|string',
            'tujuan' => 'nullable|string',
        ]);

        if ($pengeluaranBarang->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat mengubah data yang sudah diapprove'
            ], 422);
        }

        // Check stock availability
        $stock = StockBarang::where('id_barang', $request->id_barang)->first();
        $availableStock = $stock ? $stock->qty + $pengeluaranBarang->qty : $pengeluaranBarang->qty;

        if ($availableStock < $request->qty) {
            return response()->json([
                'success' => false,
                'message' => 'Stock tidak mencukupi. Stock tersedia: ' . $availableStock
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Reverse previous stock change
            $this->updateStock($pengeluaranBarang->id_barang, -$pengeluaranBarang->qty, 'keluar');

            $pengeluaranBarang->update([
                'id_barang' => $request->id_barang,
                'qty' => $request->qty,
                'status' => $request->status,
                'tujuan' => $request->tujuan,
                'updated_by' => Auth::id(),
            ]);

            // Apply new stock change
            $this->updateStock($request->id_barang, $request->qty, 'keluar');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengeluaran barang berhasil diupdate',
                'data' => $pengeluaranBarang->load(['barang', 'createdBy', 'updatedBy'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate pengeluaran barang: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PengeluaranBarang $pengeluaranBarang)
    {
        if ($pengeluaranBarang->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus data yang sudah diapprove'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Reverse stock change
            $this->updateStock($pengeluaranBarang->id_barang, -$pengeluaranBarang->qty, 'keluar');

            $pengeluaranBarang->update(['deleted_by' => Auth::id()]);
            $pengeluaranBarang->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengeluaran barang berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pengeluaran barang: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve pengeluaran barang
     */
    public function approve(PengeluaranBarang $pengeluaranBarang)
    {
        if ($pengeluaranBarang->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'Data sudah diapprove sebelumnya'
            ], 422);
        }

        $pengeluaranBarang->update([
            'is_approved' => true,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengeluaran barang berhasil diapprove',
            'data' => $pengeluaranBarang->load(['barang', 'approvedBy'])
        ]);
    }

    /**
     * Update stock barang
     */
    private function updateStock($idBarang, $qty, $type)
    {
        $stock = StockBarang::where('id_barang', $idBarang)->first();

        if ($stock) {
            if ($type === 'keluar') {
                $stock->qty -= $qty;
            } else {
                $stock->qty += $qty;
            }
            $stock->updated_by = Auth::id();
            $stock->save();
        } else {
            // Create new stock record
            StockBarang::create([
                'id_barang' => $idBarang,
                'qty' => $type === 'keluar' ? -$qty : 0,
                'created_by' => Auth::id(),
            ]);
        }
    }
}
