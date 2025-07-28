<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\StockBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebBarangController extends Controller
{
    public function index()
    {
        $barang = Barang::with(['stockBarang', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('barang.index', compact('barang'));
    }

    public function create()
    {
        return view('barang.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:barang,nama',
        ]);

        $barang = Barang::create([
            'nama' => $request->nama,
            'created_by' => Auth::id(),
        ]);

        // Create initial stock record with 0 qty
        StockBarang::create([
            'id_barang' => $barang->id,
            'qty' => 0,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('web.barang.index')
            ->with('success', 'Barang berhasil ditambahkan');
    }

    public function show(Barang $barang)
    {
        $barang->load([
            'stockBarang',
            'barangMasuk.createdBy',
            'pengeluaranBarang.createdBy',
            'history.createdBy'
        ]);

        return view('barang.show', compact('barang'));
    }

    public function edit(Barang $barang)
    {
        return view('barang.edit', compact('barang'));
    }

    public function update(Request $request, Barang $barang)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:barang,nama,' . $barang->id,
        ]);

        $barang->update([
            'nama' => $request->nama,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('web.barang.index')
            ->with('success', 'Barang berhasil diupdate');
    }

    public function destroy(Barang $barang)
    {
        $barang->update(['deleted_by' => Auth::id()]);
        $barang->delete();

        return redirect()->route('web.barang.index')
            ->with('success', 'Barang berhasil dihapus');
    }
}
