@extends('layouts.app')

@section('title', 'Tambah Barang Masuk - Warehouse Management')
@section('header', 'Tambah Barang Masuk')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Tambah Barang Masuk</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('web.barang-masuk.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="id_barang" class="form-label">Pilih Barang <span class="text-danger">*</span></label>
                        <select class="form-select @error('id_barang') is-invalid @enderror" id="id_barang" name="id_barang" required>
                            <option value="">-- Pilih Barang --</option>
                            @foreach($barang as $item)
                                <option value="{{ $item->id }}" {{ old('id_barang') == $item->id ? 'selected' : '' }}>
                                    {{ $item->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_barang')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
                        <label for="status_yang_mengembalikan" class="form-label">Status/Keterangan Pengembalian</label>
                        <textarea class="form-control @error('status_yang_mengembalikan') is-invalid @enderror"
                                  id="status_yang_mengembalikan"
                                  name="status_yang_mengembalikan"
                                  rows="3"
                                  placeholder="Masukkan keterangan atau status pengembalian (opsional)">{{ old('status_yang_mengembalikan') }}</textarea>
                        @error('status_yang_mengembalikan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Catatan:</strong> Stock akan otomatis bertambah setelah transaksi ini disimpan.
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i> Simpan
                        </button>
                        <a href="{{ route('web.barang-masuk.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
