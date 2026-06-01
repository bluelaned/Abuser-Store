<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Variant;
use App\Models\VoucherCode; // Panggil Model yang benar

class VoucherController extends Controller
{
    public function index($productId)
    {
        // Panggil relasi 'vouchers' yang ada di Model Variant
        $product = Product::with('variants.vouchers')->findOrFail($productId);

        return view('admin.vouchers.index', compact('product'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|exists:variants,id',
            'code' => 'required',
        ]);

        $codes = explode("\n", str_replace("\r", "", $request->code));
        $count = 0;

        foreach ($codes as $c) {
            $cleanCode = trim($c);
            if (!empty($cleanCode)) {
                // Simpan pakai Model VoucherCode
                VoucherCode::create([
                    'variant_id' => $request->variant_id,
                    'code' => $cleanCode,
                    'status' => 'AVAILABLE'
                ]);
                $count++;
            }
        }

        \App\Models\AdminLog::record('created', 'voucher', null, 'Added voucher codes to variant ID: ' . $request->variant_id);

        return back()->with('success', "Berhasil menambahkan $count stok voucher!");
    }
    // FUNGSI HAPUS KODE
    public function destroy($id)
    {
        // Cari kode berdasarkan ID, lalu hapus
        $voucher = VoucherCode::findOrFail($id);
        \App\Models\AdminLog::record('deleted', 'voucher', $id, 'Deleted voucher code ID: ' . $id);
        $voucher->delete();

        return back()->with('success', 'Kode voucher berhasil dibuang ke tong sampah!');
    }
}
