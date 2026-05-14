<?php

use App\Models\Product;
use App\Models\Variant;
use App\Models\VoucherCode;

// 1. Fix Product Description
$products = Product::whereNull('description')->orWhere('description', '')->get();
foreach ($products as $product) {
    $product->description = '<p>Ini adalah produk digital premium dari Abuser Store. Pengiriman instan dan kualitas terjamin.</p>';
    $product->save();
}
echo "Updated " . $products->count() . " products with empty descriptions.\n";

// 2. Fix Variant Stock (Min 1)
$variants = Variant::withCount('vouchers')->get();
$added = 0;
foreach ($variants as $variant) {
    if ($variant->vouchers_count == 0) {
        VoucherCode::create([
            'variant_id' => $variant->id,
            'code' => 'DUMMY-STOCK-' . strtoupper(Str::random(6)),
            'is_used' => false
        ]);
        $added++;
    }
}
echo "Added stock to " . $added . " variants.\n";

