<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// PENTING: Gunakan nama yang sesuai file kamu (VoucherCode)
use App\Models\VoucherCode; 

class Variant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'duration',
        'price',
        'price_usd', // WAJIB TAMBAHKAN INI
        'stock' ,
    ];  

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function vouchers()
    {
        // Pastikan ini VoucherCode::class, BUKAN Voucher::class
        return $this->hasMany(VoucherCode::class);
    }
}