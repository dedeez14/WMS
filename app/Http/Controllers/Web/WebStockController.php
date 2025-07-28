<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\StockBarang;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebStockController extends Controller
{
    public function index(Request $request)
    {
        $query = StockBarang::with(['barang', 'createdBy', 'updatedBy']);

        // Filter by search
        if ($request->has('search') && $request->search) {
            $query->whereHas('barang', function($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by stock level
        if ($request->has('stock_level')) {
            switch ($request->stock_level) {
                case 'low':
                    $query->where('qty', '<=', 10)->where('qty', '>', 0);
                    break;
                case 'out':
                    $query->where('qty', '=', 0);
                    break;
                case 'available':
                    $query->where('qty', '>', 10);
                    break;
            }
        }

        $stock = $query->orderBy('updated_at', 'desc')->paginate(15);

        // Summary data
        $summary = [
            'total_items' => StockBarang::count(),
            'total_stock' => StockBarang::sum('qty'),
            'low_stock' => StockBarang::where('qty', '<=', 10)->where('qty', '>', 0)->count(),
            'out_of_stock' => StockBarang::where('qty', '=', 0)->count()
        ];

        return view('stock.index', compact('stock', 'summary'));
    }

    public function show(StockBarang $stock)
    {
        $stock->load(['barang', 'createdBy', 'updatedBy', 'barang.barangMasuk', 'barang.pengeluaranBarang', 'barang.history']);
        return view('stock.show', compact('stock'));
    }

    public function edit(StockBarang $stock)
    {
        return view('stock.edit', compact('stock'));
    }

    public function update(Request $request, StockBarang $stock)
    {
        $request->validate([
            'qty' => 'required|integer|min:0',
            'reason' => 'nullable|string|max:500'
        ]);

        $oldQty = $stock->qty;

        $stock->update([
            'qty' => $request->qty,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('web.stock.index')
            ->with('success', 'Stock berhasil disesuaikan. Perubahan: ' . ($request->qty - $oldQty));
    }

    public function lowStock()
    {
        $lowStock = StockBarang::with(['barang'])
            ->where('qty', '<=', 10)
            ->orderBy('qty', 'asc')
            ->paginate(15);

        return view('stock.low-stock', compact('lowStock'));
    }

    public function outOfStock()
    {
        $outOfStock = StockBarang::with(['barang'])
            ->where('qty', '=', 0)
            ->orderBy('updated_at', 'desc')
            ->paginate(15);

        return view('stock.out-of-stock', compact('outOfStock'));
    }
}
