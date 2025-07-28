@extends('layouts.app')

@section('title', 'Stock Barang - Warehouse Management')
@section('header', 'Stock Barang')

@section('content')
<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stats-card">
            <div class="card-body text-center">
                <i class="bi bi-boxes display-6"></i>
                <h4 class="mt-2">{{ number_format($summary['total_items']) }}</h4>
                <p class="mb-0">Total Items</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card success">
            <div class="card-body text-center">
                <i class="bi bi-stack display-6"></i>
                <h4 class="mt-2">{{ number_format($summary['total_stock']) }}</h4>
                <p class="mb-0">Total Stock</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card warning">
            <div class="card-body text-center">
                <i class="bi bi-exclamation-triangle display-6"></i>
                <h4 class="mt-2">{{ $summary['low_stock'] }}</h4>
                <p class="mb-0">Low Stock</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card danger">
            <div class="card-body text-center">
                <i class="bi bi-x-circle display-6"></i>
                <h4 class="mt-2">{{ $summary['out_of_stock'] }}</h4>
                <p class="mb-0">Out of Stock</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0"><i class="bi bi-clipboard-data me-2"></i>Daftar Stock Barang</h5>
            </div>
            <div class="col-auto">
                <!-- Filter -->
                <form method="GET" class="d-flex gap-2">
                    <input type="text"
                           class="form-control form-control-sm"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Cari barang...">
                    <select name="stock_level" class="form-select form-select-sm">
                        <option value="">Semua Stock</option>
                        <option value="available" {{ request('stock_level') == 'available' ? 'selected' : '' }}>Available (>10)</option>
                        <option value="low" {{ request('stock_level') == 'low' ? 'selected' : '' }}>Low Stock (≤10)</option>
                        <option value="out" {{ request('stock_level') == 'out' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body">
        @if($stock->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Stock Tersedia</th>
                            <th>Status</th>
                            <th>Terakhir Update</th>
                            <th>Update Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stock as $index => $item)
                            <tr>
                                <td>{{ $stock->firstItem() + $index }}</td>
                                <td><strong>{{ $item->barang->nama }}</strong></td>
                                <td>
                                    <span class="badge bg-{{ $item->qty > 10 ? 'success' : ($item->qty > 0 ? 'warning' : 'danger') }} fs-6">
                                        {{ $item->qty }} pcs
                                    </span>
                                </td>
                                <td>
                                    @if($item->qty > 10)
                                        <span class="badge bg-success">Available</span>
                                    @elseif($item->qty > 0)
                                        <span class="badge bg-warning">Low Stock</span>
                                    @else
                                        <span class="badge bg-danger">Out of Stock</span>
                                    @endif
                                </td>
                                <td>{{ $item->updated_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $item->updatedBy->name ?? $item->createdBy->name ?? 'System' }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('web.stock.show', $item) }}" class="btn btn-sm btn-outline-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('web.stock.edit', $item) }}" class="btn btn-sm btn-outline-warning">
                                            <i class="bi bi-pencil"></i>
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
                {{ $stock->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-clipboard-data display-1 text-muted"></i>
                <h4 class="text-muted mt-3">Tidak ada data stock</h4>
                <p class="text-muted">Data stock akan muncul setelah ada transaksi barang masuk</p>
            </div>
        @endif
    </div>
</div>
@endsection
