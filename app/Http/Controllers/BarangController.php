<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $barang = Barang::with(['createdBy', 'stockBarang'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $barang
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        $barang = Barang::create([
            'nama' => $request->nama,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil ditambahkan',
            'data' => $barang->load(['createdBy'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Barang $barang)
    {
        $barang->load([
            'createdBy',
            'updatedBy',
            'stockBarang',
            'barangMasuk.approvedBy',
            'pengeluaranBarang.approvedBy',
            'history'
        ]);

        return response()->json([
            'success' => true,
            'data' => $barang
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Barang $barang)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        $barang->update([
            'nama' => $request->nama,
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil diupdate',
            'data' => $barang->load(['createdBy', 'updatedBy'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Barang $barang)
    {
        $barang->update(['deleted_by' => Auth::id()]);
        $barang->delete();

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil dihapus'
        ]);
    }

    /**
     * Get barang with current stock
     */
    public function withStock()
    {
        $barang = Barang::with(['stockBarang'])
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'nama' => $item->nama,
                    'stock' => $item->stockBarang ? $item->stockBarang->qty : 0,
                    'created_at' => $item->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $barang
        ]);
    }
}
