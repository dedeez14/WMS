<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Approval::with(['approvable', 'approvedBy']);

        // Filter by approvable type
        if ($request->has('type')) {
            $query->where('approvable_type', $request->type);
        }

        $approvals = $query->orderBy('approved_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $approvals
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'approvable_type' => 'required|string',
            'approvable_id' => 'required|integer',
            'notes' => 'nullable|string',
        ]);

        $approval = Approval::create([
            'approvable_type' => $request->approvable_type,
            'approvable_id' => $request->approvable_id,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Approval berhasil ditambahkan',
            'data' => $approval->load(['approvable', 'approvedBy'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Approval $approval)
    {
        $approval->load(['approvable', 'approvedBy']);

        return response()->json([
            'success' => true,
            'data' => $approval
        ]);
    }

    /**
     * Get approvals by type
     */
    public function getByType($type)
    {
        $approvals = Approval::with(['approvable', 'approvedBy'])
            ->where('approvable_type', $type)
            ->orderBy('approved_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $approvals
        ]);
    }

    /**
     * Get pending approvals (items that need approval)
     */
    public function pending()
    {
        // This would require checking each approvable model for items that need approval
        // For now, we'll return items that don't have approval records yet

        $pendingBarangMasuk = \App\Models\BarangMasuk::where('is_approved', false)->get();
        $pendingPengeluaran = \App\Models\PengeluaranBarang::where('is_approved', false)->get();
        $pendingHistory = \App\Models\History::where('is_approved', false)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'barang_masuk' => $pendingBarangMasuk,
                'pengeluaran_barang' => $pendingPengeluaran,
                'history' => $pendingHistory
            ]
        ]);
    }
}
