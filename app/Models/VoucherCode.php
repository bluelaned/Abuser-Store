<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherCode extends Model
{
    use HasFactory;

    // Pastikan nama tabel benar (opsional, tapi aman)
    protected $table = 'voucher_codes';

    protected $fillable = [
        'variant_id',
        'code',
        'status',
    ];

    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }
}