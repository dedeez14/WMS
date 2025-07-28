<?php

namespace App\Http\Controllers;

use App\Models\BarangKategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BarangKategoriController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kategori = BarangKategori::with(['createdBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $kategori
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

        $kategori = BarangKategori::create([
            'nama' => $request->nama,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kategori barang berhasil ditambahkan',
            'data' => $kategori->load(['createdBy'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(BarangKategori $kategori)
    {
        $kategori->load(['createdBy', 'updatedBy']);

        return response()->json([
            'success' => true,
            'data' => $kategori
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BarangKategori $kategori)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        $kategori->update([
            'nama' => $request->nama,
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kategori barang berhasil diupdate',
            'data' => $kategori->load(['createdBy', 'updatedBy'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BarangKategori $kategori)
    {
        $kategori->update(['deleted_by' => Auth::id()]);
        $kategori->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kategori barang berhasil dihapus'
        ]);
    }
}
