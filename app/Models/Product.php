<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    // Relasi ke Variant (Sudah ada)
    public function variants()
    {
        return $this->hasMany(Variant::class);
    }

    public function images() {
        return $this->hasMany(ProductImage::class);
    }

    // --- TAMBAHKAN FUNGSI INI (JEMBATAN) ---
    public function voucherCodes()
    {
        // Artinya: Ambil VoucherCode melalui perantara Variant
        return $this->hasManyThrough(VoucherCode::class, Variant::class);
    }
}