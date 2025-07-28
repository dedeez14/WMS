@extends('layouts.app')

@section('title', 'Pengeluaran Barang - Warehouse Management')
@section('header', 'Pengeluaran Barang')

@section('actions')
    <a href="{{ route('web.pengeluaran-barang.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Tambah Pengeluaran
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0"><i class="bi bi-box-arrow-up me-2"></i>Daftar Pengeluaran Barang</h5>
            </div>
            <div class="col-auto">
                <span class="badge bg-primary">Total: {{ $pengeluaranBarang->total() }} items</span>
            </div>
        </div>
    </div>
    <div class="card-body">
        @if($pengeluaranBarang->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Nama Barang</th>
                            <th>Quantity</th>
                            <th>Tujuan</th>
                            <th>Status</th>
                            <th>Approval</th>
                            <th>Dibuat Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pengeluaranBarang as $index => $item)
                            <tr>
                                <td>{{ $pengeluaranBarang->firstItem() + $index }}</td>
                                <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                <td><strong>{{ $item->barang->nama }}</strong></td>
                                <td>
                                    <span class="badge bg-danger fs-6">{{ $item->qty }} pcs</span>
                                </td>
                                <td>{{ $item->tujuan ?? '-' }}</td>
                                <td>
                                    @if($item->status)
                                        <span class="badge bg-info">{{ $item->status }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->is_approved)
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Approved
                                        </span>
                                        <br><small class="text-muted">{{ $item->approvedBy->name ?? 'System' }}</small>
                                    @else
                                        <span class="badge bg-warning">
                                            <i class="bi bi-clock"></i> Pending
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $item->createdBy->name ?? 'System' }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('web.pengeluaran-barang.show', $item) }}" class="btn btn-sm btn-outline-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if(!$item->is_approved)
                                            <a href="{{ route('web.pengeluaran-barang.edit', $item) }}" class="btn btn-sm btn-outline-warning">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="confirmApprove({{ $item->id }})">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete({{ $item->id }})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endif
                                    </div>

                                    @if(!$item->is_approved)
                                        <form id="approve-form-{{ $item->id }}" action="{{ route('web.pengeluaran-barang.approve', $item) }}" method="POST" style="display: none;">
                                            @csrf
                                            @method('PATCH')
                                        </form>

                                        <form id="delete-form-{{ $item->id }}" action="{{ route('web.pengeluaran-barang.destroy', $item) }}" method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $pengeluaranBarang->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-box-arrow-up display-1 text-muted"></i>
                <h4 class="text-muted mt-3">Belum ada data pengeluaran barang</h4>
                <p class="text-muted">Mulai dengan menambahkan pengeluaran barang pertama</p>
                <a href="{{ route('web.pengeluaran-barang.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Pengeluaran
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmApprove(id) {
    if (confirm('Apakah Anda yakin ingin menyetujui transaksi ini?')) {
        document.getElementById('approve-form-' + id).submit();
    }
}

function confirmDelete(id) {
    if (confirm('Apakah Anda yakin ingin menghapus transaksi ini?')) {
        document.getElementById('delete-form-' + id).submit();
    }
}
</script>
@endpush
