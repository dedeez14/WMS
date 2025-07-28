<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class History extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'history';

    protected $fillable = [
        'id_barang',
        'qty',
        'id_barang_masuk',
        'id_barang_keluar',
        'is_approved',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $dates = ['deleted_at', 'approved_at'];

    protected $casts = [
        'is_approved' => 'boolean',
        'approved_at' => 'datetime'
    ];

    // Relations
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }

    public function barangMasuk()
    {
        return $this->belongsTo(BarangMasuk::class, 'id_barang_masuk');
    }

    public function barangKeluar()
    {
        return $this->belongsTo(PengeluaranBarang::class, 'id_barang_keluar');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    // Polymorphic relation for approvals
    public function approvals()
    {
        return $this->morphMany(Approval::class, 'approvable');
    }
}
