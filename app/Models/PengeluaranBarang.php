<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PengeluaranBarang extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pengeluaran_barang';

    protected $fillable = [
        'id_barang',
        'qty',
        'status',
        'tujuan',
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

    public function history()
    {
        return $this->hasMany(History::class, 'id_barang_keluar');
    }

    // Polymorphic relation for approvals
    public function approvals()
    {
        return $this->morphMany(Approval::class, 'approvable');
    }
}
