<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;

class ReviewController extends Controller
{
    // Public: Halaman semua review
    public function publicIndex(Request $request)
    {
        $sort   = $request->get('sort', 'newest');
        $filter = $request->get('rating', 'all');

        $query = Review::with('user')->where('is_published', true);

        if ($filter !== 'all' && in_array($filter, ['1','2','3','4','5'])) {
            $query->where('rating', (int)$filter);
        }

        match ($sort) {
            'highest' => $query->orderBy('rating', 'desc')->orderBy('created_at', 'desc'),
            'lowest'  => $query->orderBy('rating', 'asc')->orderBy('created_at', 'desc'),
            'oldest'  => $query->orderBy('created_at', 'asc'),
            default   => $query->orderBy('created_at', 'desc'),
        };

        $reviews      = $query->paginate(24)->withQueryString();
        $totalReviews = Review::where('is_published', true)->count();
        $avgRating    = Review::where('is_published', true)->avg('rating') ?? 0;
        $starCounts   = Review::where('is_published', true)
                              ->selectRaw('rating, COUNT(*) as total')
                              ->groupBy('rating')
                              ->pluck('total', 'rating');

        return view('reviews', compact('reviews', 'totalReviews', 'avgRating', 'starCounts', 'sort', 'filter'));
    }

    // Admin: Menampilkan daftar review
    public function index()
    {
        $reviews = Review::with('user')->orderBy('created_at', 'desc')->get();
        return view('admin.reviews.index', compact('reviews'));
    }

    // Admin: Mengubah status publish
    public function togglePublish($id)
    {
        $review = Review::findOrFail($id);
        $review->update(['is_published' => !$review->is_published]);
        \App\Models\AdminLog::record('updated', 'review', $id, 'Toggled publish status for review ID: ' . $id);
        return back()->with('success', 'Status review berhasil diubah!');
    }

    // Admin: Menghapus review
    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        \App\Models\AdminLog::record('deleted', 'review', $id, 'Deleted review ID: ' . $id);
        $review->delete();
        return back()->with('success', 'Review berhasil dihapus!');
    }

    // User: Menyimpan review
    public function store(Request $request)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000'
        ]);

        $user_id = auth()->id();

        // Cek apakah sudah pernah review
        $existing = Review::where('user_id', $user_id)->first();
        if ($existing) {
            return back()->with('error', 'Anda sudah pernah memberikan ulasan.');
        }

        Review::create([
            'user_id' => $user_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_published' => false // Default butuh approve admin
        ]);

        return back()->with('success', 'Review berhasil dikirim dan menunggu persetujuan Admin!');
    }
}
