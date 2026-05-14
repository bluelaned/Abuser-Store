<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\Variant;

class ProductController extends Controller
{
    public function create() { 
        return view('admin.product_create'); 
    }

    public function index() {
        $products = Product::with('variants')->latest()->get();
        $reviews = \App\Models\Review::with('user')->where('is_published', true)->latest()->get();
        return view('index', compact('products', 'reviews')); 
    }

    public function adminDashboard() {
        $products = Product::with('variants')->latest()->get();
        return view('admin.dashboard', compact('products'));
    }

    // --- SIMPAN PRODUK BARU ---
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:internal,external',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'slider_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048', 
            'durations' => 'required|array',
            'prices' => 'required|array',
            'prices_usd' => 'required|array',
            // 👇 BARIS 'stocks' => 'required|array' DIHAPUS AJA 👇
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product = Product::create([
            'name' => $request->name,
            'type' => $request->type,
            'description' => $request->description,
            'image' => $imagePath ?? null,
        ]);

        foreach ($request->durations as $key => $duration) {
            Variant::create([
                'product_id' => $product->id,
                'duration'   => $duration,
                'price'      => $request->prices[$key],
                'price_usd'  => $request->prices_usd[$key],
                // 👇 BARIS 'stock' => ... DIHAPUS JUGA 👇
            ]);
        }

        if ($request->hasFile('slider_images')) {
            foreach ($request->file('slider_images') as $file) {
                $path = $file->store('products/sliders', 'public');
                $product->images()->create([
                    'image_path' => $path
                ]);
            }
        }

        return redirect()->route('admin.dashboard')->with('success', 'Produk Berhasil Dibuat!');
    }

    public function edit($id) {
        $product = Product::with('variants')->findOrFail($id);
        return view('admin.edit', compact('product'));
    }

    // --- UPDATE PRODUK ---
    // --- UPDATE PRODUK (VERSI DIGI: GAK NGURUSIN STOK MANUAL) ---
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:internal,external',
            // image nullable biar gak wajib upload ulang
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', 
            // [BARU] Validasi buat gambar slider yang baru diupload
            'slider_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'durations' => 'required|array',
            'prices' => 'required|array',
            'prices_usd' => 'required|array',
        ]);

        $product = Product::findOrFail($id);
        $product->name = $request->name;
        $product->type = $request->type;
        $product->description = $request->description;

        if ($request->hasFile('image')) {
            if ($product->image) { 
                Storage::disk('public')->delete($product->image); 
            }
            $product->image = $request->file('image')->store('products', 'public');
        }
        $product->save();

        if ($request->has('durations')) {
            // Hapus varian yang dibuang user
            $ids_to_keep = array_filter($request->variant_ids ?? [], fn($val) => !empty($val));
            $product->variants()->whereNotIn('id', $ids_to_keep)->delete();

            foreach ($request->durations as $index => $duration) {
                // KITA GAK UPDATE KOLOM 'stock' DI SINI
                // BIARKAN ITU DIHITUNG DARI TABLE VOUCHERS
                $variantData = [
                    'duration'  => $duration,
                    'price'     => $request->prices[$index] ?? 0,
                    'price_usd' => $request->prices_usd[$index] ?? 0,
                ];

                $variantId = $request->variant_ids[$index] ?? null;

                if (!empty($variantId)) {
                    Variant::where('id', $variantId)->update($variantData);
                } else {
                    $product->variants()->create($variantData);
                }
            }
        }

        // ========================================================
        // [BARU] LOGIKA HAPUS & TAMBAH GAMBAR SLIDER CAROUSEL
        // ========================================================

        // 1. HAPUS GAMBAR SLIDE YANG DICEKLIS (TONG SAMPAH)
        if ($request->has('delete_sliders')) {
            // Cari gambar berdasarkan ID yang diceklis, khusus buat produk ini
            $imagesToDelete = $product->images()->whereIn('id', $request->delete_sliders)->get();
            
            foreach ($imagesToDelete as $img) {
                // Hapus file fisiknya dari folder storage
                Storage::disk('public')->delete($img->image_path);
                // Hapus datanya dari database
                $img->delete();
            }
        }

        // 2. TAMBAH GAMBAR SLIDE BARU (KALAU ADA)
        if ($request->hasFile('slider_images')) {
            foreach ($request->file('slider_images') as $file) {
                // Simpan fisik gambar ke folder public/storage/products/sliders
                $path = $file->store('products/sliders', 'public');
                
                // Simpan nama path ke database product_images
                $product->images()->create([
                    'image_path' => $path
                ]);
            }
        }
        // ========================================================

        return redirect()->route('admin.dashboard')->with('success', 'Produk berhasil diupdate!');
    }

    // --- FUNGSI CHECKOUT ---
    public function checkout($id)
    {
        // Pakai Eager Loading variants
        $product = Product::with('variants')->findOrFail($id);
        
        session(['checkout_product_id' => $id]); 
        session()->save(); 

        return view('checkout', compact('product'));
    }

    public function destroy($id) {
        $product = Product::findOrFail($id);
        if ($product->image) { 
            Storage::disk('public')->delete($product->image); 
        }
        $product->delete();
        return redirect()->route('admin.dashboard')->with('success', 'Produk Dihapus');
    }
}