<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\StockBarang;
use App\Models\BarangMasuk;
use App\Models\PengeluaranBarang;
use App\Models\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $totalBarang = Barang::count();
        $totalStock = StockBarang::sum('qty');
        $lowStockItems = StockBarang::where('qty', '<=', 10)->count();
        $outOfStockItems = StockBarang::where('qty', '=', 0)->count();

        $recentBarangMasuk = BarangMasuk::with(['barang', 'createdBy'])
            ->latest()
            ->limit(5)
            ->get();

        $recentPengeluaran = PengeluaranBarang::with(['barang', 'createdBy'])
            ->latest()
            ->limit(5)
            ->get();

        $lowStockBarang = StockBarang::with('barang')
            ->where('qty', '<=', 10)
            ->orderBy('qty', 'asc')
            ->limit(10)
            ->get();

        // Monthly chart data
        $monthlyData = History::selectRaw('MONTH(created_at) as month,
                                          SUM(CASE WHEN id_barang_masuk IS NOT NULL THEN qty ELSE 0 END) as masuk,
                                          SUM(CASE WHEN id_barang_keluar IS NOT NULL THEN qty ELSE 0 END) as keluar')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('dashboard.index', compact(
            'totalBarang',
            'totalStock',
            'lowStockItems',
            'outOfStockItems',
            'recentBarangMasuk',
            'recentPengeluaran',
            'lowStockBarang',
            'monthlyData'
        ));
    }
}
