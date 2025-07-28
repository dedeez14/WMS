<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    use HasFactory;

    protected $table = 'approvals';

    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'approved_by',
        'approved_at',
        'notes'
    ];

    protected $dates = ['approved_at'];

    protected $casts = [
        'approved_at' => 'datetime'
    ];

    // Polymorphic relation
    public function approvable()
    {
        return $this->morphTo();
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
