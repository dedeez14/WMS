<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Barang extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'barang';

    protected $fillable = [
        'nama',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $dates = ['deleted_at'];

    // Relations
    public function barangMasuk()
    {
        return $this->hasMany(BarangMasuk::class, 'id_barang');
    }

    public function pengeluaranBarang()
    {
        return $this->hasMany(PengeluaranBarang::class, 'id_barang');
    }

    public function stockBarang()
    {
        return $this->hasOne(StockBarang::class, 'id_barang');
    }

    public function history()
    {
        return $this->hasMany(History::class, 'id_barang');
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
}
