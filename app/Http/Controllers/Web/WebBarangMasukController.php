<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BarangMasuk;
use App\Models\Barang;
use App\Models\StockBarang;
use App\Models\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WebBarangMasukController extends Controller
{
    public function index()
    {
        $barangMasuk = BarangMasuk::with(['barang', 'createdBy', 'approvedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('barang-masuk.index', compact('barangMasuk'));
    }

    public function create()
    {
        $barang = Barang::orderBy('nama')->get();
        return view('barang-masuk.create', compact('barang'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_barang' => 'required|exists:barang,id',
            'qty' => 'required|integer|min:1',
            'status_yang_mengembalikan' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $barangMasuk = BarangMasuk::create([
                'id_barang' => $request->id_barang,
                'qty' => $request->qty,
                'status_yang_mengembalikan' => $request->status_yang_mengembalikan,
                'created_by' => Auth::id(),
            ]);

            // Update stock
            $this->updateStock($request->id_barang, $request->qty, 'masuk');

            // Create history
            History::create([
                'id_barang' => $request->id_barang,
                'qty' => $request->qty,
                'id_barang_masuk' => $barangMasuk->id,
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return redirect()->route('web.barang-masuk.index')
                ->with('success', 'Barang masuk berhasil ditambahkan');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menambahkan barang masuk: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(BarangMasuk $barangMasuk)
    {
        $barangMasuk->load(['barang', 'createdBy', 'approvedBy', 'history']);
        return view('barang-masuk.show', compact('barangMasuk'));
    }

    public function edit(BarangMasuk $barangMasuk)
    {
        if ($barangMasuk->is_approved) {
            return redirect()->route('web.barang-masuk.index')
                ->with('error', 'Tidak dapat mengubah data yang sudah diapprove');
        }

        $barang = Barang::orderBy('nama')->get();
        return view('barang-masuk.edit', compact('barangMasuk', 'barang'));
    }

    public function update(Request $request, BarangMasuk $barangMasuk)
    {
        if ($barangMasuk->is_approved) {
            return redirect()->route('web.barang-masuk.index')
                ->with('error', 'Tidak dapat mengubah data yang sudah diapprove');
        }

        $request->validate([
            'id_barang' => 'required|exists:barang,id',
            'qty' => 'required|integer|min:1',
            'status_yang_mengembalikan' => 'nullable|string|max:500',
        ]);

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

            return redirect()->route('web.barang-masuk.index')
                ->with('success', 'Barang masuk berhasil diupdate');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal mengupdate barang masuk: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(BarangMasuk $barangMasuk)
    {
        if ($barangMasuk->is_approved) {
            return redirect()->route('web.barang-masuk.index')
                ->with('error', 'Tidak dapat menghapus data yang sudah diapprove');
        }

        DB::beginTransaction();
        try {
            // Reverse stock change
            $this->updateStock($barangMasuk->id_barang, -$barangMasuk->qty, 'masuk');

            $barangMasuk->update(['deleted_by' => Auth::id()]);
            $barangMasuk->delete();

            DB::commit();

            return redirect()->route('web.barang-masuk.index')
                ->with('success', 'Barang masuk berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('web.barang-masuk.index')
                ->with('error', 'Gagal menghapus barang masuk: ' . $e->getMessage());
        }
    }

    public function approve(BarangMasuk $barangMasuk)
    {
        if ($barangMasuk->is_approved) {
            return redirect()->route('web.barang-masuk.index')
                ->with('error', 'Data sudah diapprove sebelumnya');
        }

        $barangMasuk->update([
            'is_approved' => true,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('web.barang-masuk.index')
            ->with('success', 'Barang masuk berhasil diapprove');
    }

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
            StockBarang::create([
                'id_barang' => $idBarang,
                'qty' => $type === 'masuk' ? $qty : 0,
                'created_by' => Auth::id(),
            ]);
        }
    }
}
