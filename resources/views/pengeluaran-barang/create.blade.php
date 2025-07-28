@extends('layouts.app')

@section('title', 'Tambah Pengeluaran Barang - Warehouse Management')
@section('header', 'Tambah Pengeluaran Barang')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Tambah Pengeluaran Barang</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('web.pengeluaran-barang.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="id_barang" class="form-label">Pilih Barang <span class="text-danger">*</span></label>
                        <select class="form-select @error('id_barang') is-invalid @enderror" id="id_barang" name="id_barang" required onchange="updateStockInfo()">
                            <option value="">-- Pilih Barang --</option>
                            @foreach($barang as $item)
                                <option value="{{ $item->id }}"
                                        data-stock="{{ $item->stockBarang ? $item->stockBarang->qty : 0 }}"
                                        {{ old('id_barang') == $item->id ? 'selected' : '' }}>
                                    {{ $item->nama }} (Stock: {{ $item->stockBarang ? $item->stockBarang->qty : 0 }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_barang')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div id="stock-info" class="form-text"></div>
                    </div>

                    <div class="mb-3">
                        <label for="qty" class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number"
                               class="form-control @error('qty') is-invalid @enderror"
                               id="qty"
                               name="qty"
                               value="{{ old('qty') }}"
                               placeholder="Masukkan jumlah barang"
                               min="1"
                               required>
                        @error('qty')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="tujuan" class="form-label">Tujuan</label>
                        <input type="text"
                               class="form-control @error('tujuan') is-invalid @enderror"
                               id="tujuan"
                               name="tujuan"
                               value="{{ old('tujuan') }}"
                               placeholder="Masukkan tujuan penggunaan barang">
                        @error('tujuan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                            <option value="">-- Pilih Status --</option>
                            <option value="Dipinjam" {{ old('status') == 'Dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                            <option value="Digunakan" {{ old('status') == 'Digunakan' ? 'selected' : '' }}>Digunakan</option>
                            <option value="Dijual" {{ old('status') == 'Dijual' ? 'selected' : '' }}>Dijual</option>
                            <option value="Rusak" {{ old('status') == 'Rusak' ? 'selected' : '' }}>Rusak</option>
                            <option value="Hilang" {{ old('status') == 'Hilang' ? 'selected' : '' }}>Hilang</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Perhatian:</strong> Stock akan otomatis berkurang setelah transaksi ini disimpan. Pastikan quantity tidak melebihi stock yang tersedia.
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i> Simpan
                        </button>
                        <a href="{{ route('web.pengeluaran-barang.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateStockInfo() {
    const select = document.getElementById('id_barang');
    const stockInfo = document.getElementById('stock-info');
    const qtyInput = document.getElementById('qty');

    if (select.value) {
        const selectedOption = select.options[select.selectedIndex];
        const stock = parseInt(selectedOption.dataset.stock);

        if (stock > 0) {
            stockInfo.innerHTML = `<span class="text-success">Stock tersedia: ${stock} pcs</span>`;
            qtyInput.max = stock;
        } else {
            stockInfo.innerHTML = `<span class="text-danger">Stock tidak tersedia</span>`;
            qtyInput.max = 0;
        }
    } else {
        stockInfo.innerHTML = '';
        qtyInput.removeAttribute('max');
    }
}

// Update stock info on page load if there's a selected value
document.addEventListener('DOMContentLoaded', function() {
    updateStockInfo();
});
</script>
@endpush
