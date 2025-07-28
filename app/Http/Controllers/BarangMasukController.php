<?php

namespace App\Http\Controllers;

use App\Models\BarangMasuk;
use App\Models\Barang;
use App\Models\StockBarang;
use App\Models\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BarangMasukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $barangMasuk = BarangMasuk::with(['barang', 'createdBy', 'approvedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $barangMasuk
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
            'status_yang_mengembalikan' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $barangMasuk = BarangMasuk::create([
                'id_barang' => $request->id_barang,
                'qty' => $request->qty,
                'status_yang_mengembalikan' => $request->status_yang_mengembalikan,
                'created_by' => Auth::id(),
            ]);

            // Update or create stock
            $this->updateStock($request->id_barang, $request->qty, 'masuk');

            // Create history record
            History::create([
                'id_barang' => $request->id_barang,
                'qty' => $request->qty,
                'id_barang_masuk' => $barangMasuk->id,
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Barang masuk berhasil ditambahkan',
                'data' => $barangMasuk->load(['barang', 'createdBy'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan barang masuk: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(BarangMasuk $barangMasuk)
    {
        $barangMasuk->load([
            'barang',
            'createdBy',
            'updatedBy',
            'approvedBy',
            'history'
        ]);

        return response()->json([
            'success' => true,
            'data' => $barangMasuk
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BarangMasuk $barangMasuk)
    {
        $request->validate([
            'id_barang' => 'required|exists:barang,id',
            'qty' => 'required|integer|min:1',
            'status_yang_mengembalikan' => 'nullable|string',
        ]);

        if ($barangMasuk->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat mengubah data yang sudah diapprove'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Reverse previous stock change
            $this->updateStock($barangMasuk->id_barang, -$barangMasuk->qty, 'masuk');

            $barangMasuk->update([
                'id_barang' => $request->id_barang,
                'qty' => $request->qty,
                'status_yang_mengembalikan' => $request->status_yang_mengembalikan,
                'updated_by' => Auth::id(),
            ]);

            // Apply new stock change
            $this->updateStock($request->id_barang, $request->qty, 'masuk');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Barang masuk berhasil diupdate',
                'data' => $barangMasuk->load(['barang', 'createdBy', 'updatedBy'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate barang masuk: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BarangMasuk $barangMasuk)
    {
        if ($barangMasuk->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus data yang sudah diapprove'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Reverse stock change
            $this->updateStock($barangMasuk->id_barang, -$barangMasuk->qty, 'masuk');

            $barangMasuk->update(['deleted_by' => Auth::id()]);
            $barangMasuk->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Barang masuk berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus barang masuk: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve barang masuk
     */
    public function approve(BarangMasuk $barangMasuk)
    {
        if ($barangMasuk->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'Data sudah diapprove sebelumnya'
            ], 422);
        }

        $barangMasuk->update([
            'is_approved' => true,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Barang masuk berhasil diapprove',
            'data' => $barangMasuk->load(['barang', 'approvedBy'])
        ]);
    }

    /**
     * Update stock barang
     */
    private function updateStock($idBarang, $qty, $type)
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
    }
}
