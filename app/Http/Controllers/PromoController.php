<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Promo;

class PromoController extends Controller
{
    // TAMPILKAN HALAMAN PROMO
    public function index()
    {
        $promos = Promo::with('product')->latest()->get();
        $products = \App\Models\Product::all();
        return view('admin.promos.index', compact('promos', 'products'));
    }

    // SIMPAN PROMO BARU
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:promos,code',
            'value' => 'required|numeric',
            'type' => 'required',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit_per_user' => 'nullable|integer|min:0',
            'min_qty' => 'nullable|integer|min:1',
            'product_id' => 'nullable|exists:products,id'
        ]);

        Promo::create([
            'code' => strtoupper($request->code),
            'type' => $request->type, // 'percent' atau 'fixed'
            'value' => $request->value,
            'max_discount' => $request->max_discount ?? 0,
            'usage_limit_per_user' => $request->usage_limit_per_user ?? 0,
            'min_qty' => $request->min_qty ?? 1,
            'product_id' => $request->product_id
        ]);

        \App\Models\AdminLog::record('created', 'promo', null, 'Created promo code: ' . strtoupper($request->code));

        return back()->with('success', 'Promo berhasil dibuat!');
    }

    // HAPUS PROMO
    public function destroy($id)
    {
        $promo = Promo::findOrFail($id);
        \App\Models\AdminLog::record('deleted', 'promo', $id, 'Deleted promo code: ' . $promo->code);
        $promo->delete();
        return back()->with('success', 'Promo dihapus.');
    }
}
