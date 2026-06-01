<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlistItems = auth()->user()->wishlists()->with('product.variants')->latest()->get();
        return view('wishlist', compact('wishlistItems'));
    }

    public function toggle($productId)
    {
        $user = auth()->user();
        $product = Product::findOrFail($productId);

        $existing = Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->delete();
            $inWishlist = false;
        } else {
            Wishlist::create(['user_id' => $user->id, 'product_id' => $productId]);
            $inWishlist = true;
        }

        return response()->json(['in_wishlist' => $inWishlist]);
    }
}
