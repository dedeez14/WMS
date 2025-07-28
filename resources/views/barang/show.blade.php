@extends('layouts.app')

@section('title', 'Detail Barang - Warehouse Management')
@section('header', 'Detail Barang')

@section('actions')
    <a href="{{ route('web.barang.edit', $barang) }}" class="btn btn-warning">
        <i class="bi bi-pencil me-1"></i> Edit
    </a>
    <a href="{{ route('web.barang.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <!-- Info Barang -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informasi Barang</h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Nama</strong></td>
                        <td>{{ $barang->nama }}</td>
                    </tr>
                    <tr>
                        <td><strong>Stock Saat Ini</strong></td>
                        <td>
                            @if($barang->stockBarang)
                                <span class="badge bg-{{ $barang->stockBarang->qty > 10 ? 'success' : ($barang->stockBarang->qty > 0 ? 'warning' : 'danger') }} fs-6">
                                    {{ $barang->stockBarang->qty }} pcs
                                </span>
                            @else
                                <span class="badge bg-secondary">No Stock</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Dibuat Oleh</strong></td>
                        <td>{{ $barang->createdBy->name ?? 'System' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Tanggal Dibuat</strong></td>
                        <td>{{ $barang->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @if($barang->updated_at != $barang->created_at)
                    <tr>
                        <td><strong>Terakhir Diupdate</strong></td>
                        <td>{{ $barang->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Riwayat Barang Masuk -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-box-arrow-in-down me-2"></i>Riwayat Barang Masuk</h6>
            </div>
            <div class="card-body">
                @if($barang->barangMasuk->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Qty</th>
                                    <th>Status</th>
                                    <th>Dibuat Oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($barang->barangMasuk->take(5) as $masuk)
                                    <tr>
                                        <td>{{ $masuk->created_at->format('d/m/Y') }}</td>
                                        <td><span class="badge bg-success">+{{ $masuk->qty }}</span></td>
                                        <td>
                                            @if($masuk->is_approved)
                                                <span class="badge bg-success">Approved</span>
                                            @else
                                                <span class="badge bg-warning">Pending</span>
                                            @endif
                                        </td>
                                        <td>{{ $masuk->createdBy->name ?? 'System' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($barang->barangMasuk->count() > 5)
                        <div class="text-center">
                            <a href="{{ route('web.barang-masuk.index') }}?barang={{ $barang->id }}" class="btn btn-sm btn-outline-primary">
                                Lihat Semua ({{ $barang->barangMasuk->count() }})
                            </a>
                        </div>
                    @endif
                @else
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-inbox display-6"></i>
                        <p class="mt-2">Belum ada riwayat barang masuk</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Riwayat Barang Keluar -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-box-arrow-up me-2"></i>Riwayat Barang Keluar</h6>
            </div>
            <div class="card-body">
                @if($barang->pengeluaranBarang->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Qty</th>
                                    <th>Tujuan</th>
                                    <th>Status</th>
                                    <th>Dibuat Oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($barang->pengeluaranBarang->take(5) as $keluar)
                                    <tr>
                                        <td>{{ $keluar->created_at->format('d/m/Y') }}</td>
                                        <td><span class="badge bg-danger">-{{ $keluar->qty }}</span></td>
                                        <td>{{ $keluar->tujuan ?? '-' }}</td>
                                        <td>
                                            @if($keluar->is_approved)
                                                <span class="badge bg-success">Approved</span>
                                            @else
                                                <span class="badge bg-warning">Pending</span>
                                            @endif
                                        </td>
                                        <td>{{ $keluar->createdBy->name ?? 'System' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($barang->pengeluaranBarang->count() > 5)
                        <div class="text-center">
                            <a href="{{ route('web.pengeluaran-barang.index') }}?barang={{ $barang->id }}" class="btn btn-sm btn-outline-primary">
                                Lihat Semua ({{ $barang->pengeluaranBarang->count() }})
                            </a>
                        </div>
                    @endif
                @else
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-inbox display-6"></i>
                        <p class="mt-2">Belum ada riwayat barang keluar</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
