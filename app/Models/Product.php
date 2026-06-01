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

    // --- PROFIT ACCESSORS ---
    public function getProfitAttribute(): float
    {
        $sell   = (float) ($this->selling_price ?? 0);
        $cost   = (float) ($this->official_price ?? 0);
        return max(0, $sell - $cost);
    }

    public function getProfitPercentAttribute(): float
    {
        $cost = (float) ($this->official_price ?? 0);
        if ($cost <= 0) return 0;
        return round((($this->profit / $cost) * 100), 1);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }
}
