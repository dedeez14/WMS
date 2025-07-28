@extends('layouts.app')

@section('title', 'Dashboard - Warehouse Management')
@section('header', 'Dashboard')

@section('content')
<div class="row mb-4">
    <!-- Stats Cards -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Total Barang</div>
                        <div class="h5 mb-0 font-weight-bold">{{ $totalBarang }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-box-seam display-6"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card success">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Total Stock</div>
                        <div class="h5 mb-0 font-weight-bold">{{ number_format($totalStock) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-boxes display-6"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card warning">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Low Stock</div>
                        <div class="h5 mb-0 font-weight-bold">{{ $lowStockItems }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-exclamation-triangle display-6"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card danger">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Out of Stock</div>
                        <div class="h5 mb-0 font-weight-bold">{{ $outOfStockItems }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-x-circle display-6"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Chart -->
    <div class="col-xl-8 col-lg-7">
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-bar-chart me-1"></i>
                Stock Movement Chart ({{ date('Y') }})
            </div>
            <div class="card-body">
                <canvas id="stockChart" width="100%" height="40"></canvas>
            </div>
        </div>
    </div>

    <!-- Low Stock Alert -->
    <div class="col-xl-4 col-lg-5">
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-exclamation-triangle me-1"></i>
                Low Stock Alert
            </div>
            <div class="card-body">
                @if($lowStockBarang->count() > 0)
                    @foreach($lowStockBarang as $item)
                        <div class="d-flex align-items-center border-bottom py-2">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">{{ $item->barang->nama }}</h6>
                                <small class="text-muted">Stock: {{ $item->qty }}</small>
                            </div>
                            <span class="badge bg-warning">Low</span>
                        </div>
                    @endforeach
                    <div class="text-center mt-3">
                        <a href="{{ route('web.stock.low-stock') }}" class="btn btn-sm btn-outline-warning">
                            View All <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                @else
                    <div class="text-center text-muted">
                        <i class="bi bi-check-circle display-4"></i>
                        <p class="mt-2">No low stock items</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Barang Masuk -->
    <div class="col-xl-6 col-lg-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-box-arrow-in-down me-1"></i>
                Recent Barang Masuk
            </div>
            <div class="card-body">
                @if($recentBarangMasuk->count() > 0)
                    @foreach($recentBarangMasuk as $item)
                        <div class="d-flex align-items-center border-bottom py-2">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">{{ $item->barang->nama }}</h6>
                                <small class="text-muted">
                                    Qty: {{ $item->qty }} |
                                    {{ $item->created_at->diffForHumans() }}
                                </small>
                            </div>
                            <span class="badge bg-{{ $item->is_approved ? 'success' : 'warning' }}">
                                {{ $item->is_approved ? 'Approved' : 'Pending' }}
                            </span>
                        </div>
                    @endforeach
                    <div class="text-center mt-3">
                        <a href="{{ route('web.barang-masuk.index') }}" class="btn btn-sm btn-outline-primary">
                            View All <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                @else
                    <div class="text-center text-muted">
                        <i class="bi bi-inbox display-4"></i>
                        <p class="mt-2">No recent items</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Pengeluaran -->
    <div class="col-xl-6 col-lg-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-box-arrow-up me-1"></i>
                Recent Barang Keluar
            </div>
            <div class="card-body">
                @if($recentPengeluaran->count() > 0)
                    @foreach($recentPengeluaran as $item)
                        <div class="d-flex align-items-center border-bottom py-2">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">{{ $item->barang->nama }}</h6>
                                <small class="text-muted">
                                    Qty: {{ $item->qty }} |
                                    {{ $item->created_at->diffForHumans() }}
                                </small>
                            </div>
                            <span class="badge bg-{{ $item->is_approved ? 'success' : 'warning' }}">
                                {{ $item->is_approved ? 'Approved' : 'Pending' }}
                            </span>
                        </div>
                    @endforeach
                    <div class="text-center mt-3">
                        <a href="{{ route('web.pengeluaran-barang.index') }}" class="btn btn-sm btn-outline-primary">
                            View All <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                @else
                    <div class="text-center text-muted">
                        <i class="bi bi-inbox display-4"></i>
                        <p class="mt-2">No recent items</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Stock Movement Chart
const ctx = document.getElementById('stockChart').getContext('2d');
const chartData = @json($monthlyData);

const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
const masukData = new Array(12).fill(0);
const keluarData = new Array(12).fill(0);

chartData.forEach(item => {
    masukData[item.month - 1] = item.masuk;
    keluarData[item.month - 1] = item.keluar;
});

new Chart(ctx, {
    type: 'line',
    data: {
        labels: months,
        datasets: [{
            label: 'Barang Masuk',
            data: masukData,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.1
        }, {
            label: 'Barang Keluar',
            data: keluarData,
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.1)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
@endpush
