<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PengeluaranBarang;
use App\Models\Barang;
use App\Models\StockBarang;
use App\Models\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WebPengeluaranBarangController extends Controller
{
    public function index()
    {
        $pengeluaranBarang = PengeluaranBarang::with(['barang', 'createdBy', 'approvedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('pengeluaran-barang.index', compact('pengeluaranBarang'));
    }

    public function create()
    {
        $barang = Barang::with('stockBarang')->orderBy('nama')->get();
        return view('pengeluaran-barang.create', compact('barang'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_barang' => 'required|exists:barang,id',
            'qty' => 'required|integer|min:1',
            'status' => 'nullable|string|max:255',
            'tujuan' => 'nullable|string|max:255',
        ]);

        // Check stock availability
        $stock = StockBarang::where('id_barang', $request->id_barang)->first();
        if (!$stock || $stock->qty < $request->qty) {
            return redirect()->back()
                ->with('error', 'Stock tidak mencukupi. Stock tersedia: ' . ($stock ? $stock->qty : 0))
                ->withInput();
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

            // Create history
            History::create([
                'id_barang' => $request->id_barang,
                'qty' => $request->qty,
                'id_barang_keluar' => $pengeluaranBarang->id,
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return redirect()->route('web.pengeluaran-barang.index')
                ->with('success', 'Pengeluaran barang berhasil ditambahkan');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menambahkan pengeluaran barang: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(PengeluaranBarang $pengeluaranBarang)
    {
        $pengeluaranBarang->load(['barang', 'createdBy', 'approvedBy', 'history']);
        return view('pengeluaran-barang.show', compact('pengeluaranBarang'));
    }

    public function edit(PengeluaranBarang $pengeluaranBarang)
    {
        if ($pengeluaranBarang->is_approved) {
            return redirect()->route('web.pengeluaran-barang.index')
                ->with('error', 'Tidak dapat mengubah data yang sudah diapprove');
        }

        $barang = Barang::with('stockBarang')->orderBy('nama')->get();
        return view('pengeluaran-barang.edit', compact('pengeluaranBarang', 'barang'));
    }

    public function update(Request $request, PengeluaranBarang $pengeluaranBarang)
    {
        if ($pengeluaranBarang->is_approved) {
            return redirect()->route('web.pengeluaran-barang.index')
                ->with('error', 'Tidak dapat mengubah data yang sudah diapprove');
        }

        $request->validate([
            'id_barang' => 'required|exists:barang,id',
            'qty' => 'required|integer|min:1',
            'status' => 'nullable|string|max:255',
            'tujuan' => 'nullable|string|max:255',
        ]);

        // Check stock availability
        $stock = StockBarang::where('id_barang', $request->id_barang)->first();
        $availableStock = $stock ? $stock->qty + $pengeluaranBarang->qty : $pengeluaranBarang->qty;

        if ($availableStock < $request->qty) {
            return redirect()->back()
                ->with('error', 'Stock tidak mencukupi. Stock tersedia: ' . $availableStock)
                ->withInput();
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

            return redirect()->route('web.pengeluaran-barang.index')
                ->with('success', 'Pengeluaran barang berhasil diupdate');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal mengupdate pengeluaran barang: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(PengeluaranBarang $pengeluaranBarang)
    {
        if ($pengeluaranBarang->is_approved) {
            return redirect()->route('web.pengeluaran-barang.index')
                ->with('error', 'Tidak dapat menghapus data yang sudah diapprove');
        }

        DB::beginTransaction();
        try {
            // Reverse stock change
            $this->updateStock($pengeluaranBarang->id_barang, -$pengeluaranBarang->qty, 'keluar');

            $pengeluaranBarang->update(['deleted_by' => Auth::id()]);
            $pengeluaranBarang->delete();

            DB::commit();

            return redirect()->route('web.pengeluaran-barang.index')
                ->with('success', 'Pengeluaran barang berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('web.pengeluaran-barang.index')
                ->with('error', 'Gagal menghapus pengeluaran barang: ' . $e->getMessage());
        }
    }

    public function approve(PengeluaranBarang $pengeluaranBarang)
    {
        if ($pengeluaranBarang->is_approved) {
            return redirect()->route('web.pengeluaran-barang.index')
                ->with('error', 'Data sudah diapprove sebelumnya');
        }

        $pengeluaranBarang->update([
            'is_approved' => true,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('web.pengeluaran-barang.index')
            ->with('success', 'Pengeluaran barang berhasil diapprove');
    }

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
        }
    }
}
