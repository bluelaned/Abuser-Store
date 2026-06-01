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

    public function index(Request $request) {
        $query = Product::with('variants')->latest();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $products = $query->get();
        $categories = Product::whereNotNull('category')->where('category', '!=', '')->distinct()->orderBy('category')->pluck('category');
        $reviews = \App\Models\Review::with('user')->where('is_published', true)->latest()->get();
        return view('index', compact('products', 'reviews', 'categories'));
    }

    public function adminDashboard() {
        $products = Product::with('variants')->latest()->get();

        // Stock alert: internal products with variants having fewer than 5 available vouchers
        $lowStockVariants = \App\Models\Variant::with('product')
            ->whereHas('product', fn($q) => $q->where('type', 'internal'))
            ->withCount(['vouchers as available_stock' => fn($q) => $q->where('status', 'AVAILABLE')])
            ->having('available_stock', '<', 5)
            ->orderBy('available_stock', 'asc')
            ->get();

        return view('admin.dashboard', compact('products', 'lowStockVariants'));
    }

    // --- SIMPAN PRODUK BARU ---
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:internal,external',
            'delivery_method' => 'required|in:serial,gift',
            'official_price' => 'nullable|numeric|min:0',
            'selling_price'  => 'nullable|numeric|min:0',
            'profit_currency' => 'nullable|string|max:10',
            'category' => 'nullable|string|max:100',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'slider_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'durations' => 'required|array',
            'durations.*' => 'required|string',
            'prices_amount' => 'required|array',
            'prices_amount.*' => 'required|numeric|min:0',
            'currencies' => 'required|array',
            'currencies.*' => 'required|string|max:10',
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product = Product::create([
            'name'            => $request->name,
            'type'            => $request->type,
            'delivery_method' => $request->delivery_method,
            'official_price'  => $request->official_price ?? null,
            'selling_price'   => $request->selling_price  ?? null,
            'profit_currency' => strtoupper($request->profit_currency ?? 'USD'),
            'description'     => $request->description,
            'category'        => $request->category ?? null,
            'image'           => $imagePath ?? null,
        ]);

        foreach ($request->durations as $key => $duration) {
            $amount   = (float) ($request->prices_amount[$key] ?? 0);
            $currency = strtoupper($request->currencies[$key] ?? 'USD');
            // Maintain legacy columns for backward compat
            $priceUsd = $currency === 'USD' ? $amount : ($currency === 'IDR' ? $amount / 15500 : $amount);
            $priceIdr = $currency === 'IDR' ? (int) $amount : (int) round($amount * 15500);

            Variant::create([
                'product_id'   => $product->id,
                'duration'     => $duration,
                'price'        => $priceIdr,
                'price_usd'    => $priceUsd,
                'price_amount' => $amount,
                'currency'     => $currency,
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
            'delivery_method' => 'required|in:serial,gift',
            'official_price' => 'nullable|numeric|min:0',
            'selling_price'  => 'nullable|numeric|min:0',
            'profit_currency' => 'nullable|string|max:10',
            'category' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'slider_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'durations' => 'required|array',
            'durations.*' => 'required|string',
            'prices_amount' => 'required|array',
            'prices_amount.*' => 'required|numeric|min:0',
            'currencies' => 'required|array',
            'currencies.*' => 'required|string|max:10',
        ]);

        $product = Product::findOrFail($id);
        $product->name            = $request->name;
        $product->type            = $request->type;
        $product->delivery_method = $request->delivery_method;
        $product->official_price  = $request->official_price ?? null;
        $product->selling_price   = $request->selling_price  ?? null;
        $product->profit_currency = strtoupper($request->profit_currency ?? 'USD');
        $product->description     = $request->description;
        $product->category        = $request->category ?? null;

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
                $amount   = (float) ($request->prices_amount[$index] ?? 0);
                $currency = strtoupper($request->currencies[$index] ?? 'USD');
                $priceUsd = $currency === 'USD' ? $amount : ($currency === 'IDR' ? $amount / 15500 : $amount);
                $priceIdr = $currency === 'IDR' ? (int) $amount : (int) round($amount * 15500);

                $variantData = [
                    'duration'     => $duration,
                    'price'        => $priceIdr,
                    'price_usd'    => $priceUsd,
                    'price_amount' => $amount,
                    'currency'     => $currency,
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
        \Log::warning('ADMIN ACTION: Product Deleted by User ID: ' . auth()->id() . ' | Product Name: ' . $product->name);
        \App\Models\AdminLog::record('deleted', 'product', $product->id, 'Deleted product: ' . $product->name);
        $product->delete();
        return redirect()->route('admin.dashboard')->with('success', 'Produk Dihapus');
    }
}
