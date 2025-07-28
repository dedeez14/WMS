@extends('layouts.app')

@section('title', 'Low Stock Alert - Warehouse Management')
@section('header', 'Low Stock Alert')

@section('actions')
    <a href="{{ route('web.stock.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Stock
    </a>
@endsection

@section('content')
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <strong>Peringatan:</strong> Berikut adalah daftar barang dengan stock rendah (≤10 pcs). Segera lakukan pengadaan untuk menghindari kehabisan stock.
</div>

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Low Stock Items</h5>
            </div>
            <div class="col-auto">
                <span class="badge bg-warning">Total: {{ $lowStock->total() }} items</span>
            </div>
        </div>
    </div>
    <div class="card-body">
        @if($lowStock->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Stock Tersisa</th>
                            <th>Status</th>
                            <th>Terakhir Update</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lowStock as $index => $item)
                            <tr class="{{ $item->qty == 0 ? 'table-danger' : 'table-warning' }}">
                                <td>{{ $lowStock->firstItem() + $index }}</td>
                                <td>
                                    <strong>{{ $item->barang->nama }}</strong>
                                    @if($item->qty == 0)
                                        <span class="badge bg-danger ms-2">URGENT</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $item->qty > 0 ? 'warning' : 'danger' }} fs-6">
                                        {{ $item->qty }} pcs
                                    </span>
                                </td>
                                <td>
                                    @if($item->qty == 0)
                                        <span class="badge bg-danger">Out of Stock</span>
                                    @else
                                        <span class="badge bg-warning">Low Stock</span>
                                    @endif
                                </td>
                                <td>{{ $item->updated_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('web.stock.show', $item) }}" class="btn btn-sm btn-outline-info">
                                            <i class="bi bi-eye"></i> Detail
                                        </a>
                                        <a href="{{ route('web.barang-masuk.create') }}?barang={{ $item->barang->id }}" class="btn btn-sm btn-success">
                                            <i class="bi bi-plus-circle"></i> Tambah Stock
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $lowStock->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-check-circle display-1 text-success"></i>
                <h4 class="text-success mt-3">Semua stock dalam kondisi baik!</h4>
                <p class="text-muted">Tidak ada barang dengan stock rendah saat ini</p>
                <a href="{{ route('web.stock.index') }}" class="btn btn-primary">
                    <i class="bi bi-clipboard-data me-1"></i> Lihat Semua Stock
                </a>
            </div>
        @endif
    </div>
</div>

@if($lowStock->count() > 0)
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Rekomendasi Tindakan</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Segera lakukan pengadaan untuk barang dengan stock 0
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Rencanakan pembelian untuk barang dengan stock rendah
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Evaluasi pola penggunaan barang secara berkala
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Set up automatic alert untuk stock minimum
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Quick Stats</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-danger">{{ $lowStock->where('qty', 0)->count() }}</h4>
                        <small class="text-muted">Out of Stock</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-warning">{{ $lowStock->where('qty', '>', 0)->count() }}</h4>
                        <small class="text-muted">Low Stock</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
