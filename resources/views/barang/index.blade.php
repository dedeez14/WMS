@extends('layouts.app')

@section('title', 'Data Barang - Warehouse Management')
@section('header', 'Data Barang')

@section('actions')
    <a href="{{ route('web.barang.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Tambah Barang
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Daftar Barang</h5>
            </div>
            <div class="col-auto">
                <span class="badge bg-primary">Total: {{ $barang->total() }} items</span>
            </div>
        </div>
    </div>
    <div class="card-body">
        @if($barang->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Stock Tersedia</th>
                            <th>Dibuat Oleh</th>
                            <th>Tanggal Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($barang as $index => $item)
                            <tr>
                                <td>{{ $barang->firstItem() + $index }}</td>
                                <td>
                                    <strong>{{ $item->nama }}</strong>
                                </td>
                                <td>
                                    @if($item->stockBarang)
                                        <span class="badge bg-{{ $item->stockBarang->qty > 10 ? 'success' : ($item->stockBarang->qty > 0 ? 'warning' : 'danger') }}">
                                            {{ $item->stockBarang->qty }} pcs
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">No Stock</span>
                                    @endif
                                </td>
                                <td>{{ $item->createdBy->name ?? 'System' }}</td>
                                <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('web.barang.show', $item) }}" class="btn btn-sm btn-outline-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('web.barang.edit', $item) }}" class="btn btn-sm btn-outline-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete({{ $item->id }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>

                                    <form id="delete-form-{{ $item->id }}" action="{{ route('web.barang.destroy', $item) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $barang->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-box-seam display-1 text-muted"></i>
                <h4 class="text-muted mt-3">Belum ada data barang</h4>
                <p class="text-muted">Mulai dengan menambahkan barang pertama</p>
                <a href="{{ route('web.barang.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Barang
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(id) {
    if (confirm('Apakah Anda yakin ingin menghapus barang ini?')) {
        document.getElementById('delete-form-' + id).submit();
    }
}
</script>
@endpush
