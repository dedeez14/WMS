<?php

namespace App\Http\Controllers;

use App\Models\History;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = History::with([
            'barang',
            'barangMasuk',
            'barangKeluar',
            'createdBy',
            'approvedBy'
        ]);

        // Filter by barang
        if ($request->has('id_barang')) {
            $query->where('id_barang', $request->id_barang);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Filter by type (masuk/keluar)
        if ($request->has('type')) {
            if ($request->type === 'masuk') {
                $query->whereNotNull('id_barang_masuk');
            } elseif ($request->type === 'keluar') {
                $query->whereNotNull('id_barang_keluar');
            }
        }

        $history = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(History $history)
    {
        $history->load([
            'barang',
            'barangMasuk',
            'barangKeluar',
            'createdBy',
            'updatedBy',
            'approvedBy'
        ]);

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }

    /**
     * Get history by barang
     */
    public function getByBarang($barangId)
    {
        $history = History::with([
            'barangMasuk',
            'barangKeluar',
            'createdBy',
            'approvedBy'
        ])
        ->where('id_barang', $barangId)
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }

    /**
     * Get stock movement report
     */
    public function stockMovement(Request $request)
    {
        $query = History::with(['barang']);

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $movements = $query->get()->groupBy('id_barang')->map(function($histories) {
            $barang = $histories->first()->barang;
            $totalMasuk = $histories->whereNotNull('id_barang_masuk')->sum('qty');
            $totalKeluar = $histories->whereNotNull('id_barang_keluar')->sum('qty');

            return [
                'barang' => $barang,
                'total_masuk' => $totalMasuk,
                'total_keluar' => $totalKeluar,
                'net_movement' => $totalMasuk - $totalKeluar,
                'total_transactions' => $histories->count()
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $movements
        ]);
    }

    /**
     * Get daily report
     */
    public function dailyReport(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));

        $history = History::with(['barang', 'barangMasuk', 'barangKeluar'])
            ->whereDate('created_at', $date)
            ->get();

        $masuk = $history->whereNotNull('id_barang_masuk');
        $keluar = $history->whereNotNull('id_barang_keluar');

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $date,
                'total_transactions' => $history->count(),
                'barang_masuk' => [
                    'count' => $masuk->count(),
                    'total_qty' => $masuk->sum('qty'),
                    'items' => $masuk->values()
                ],
                'barang_keluar' => [
                    'count' => $keluar->count(),
                    'total_qty' => $keluar->sum('qty'),
                    'items' => $keluar->values()
                ]
            ]
        ]);
    }
}
