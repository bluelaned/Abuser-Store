<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show($name, $id)
    {
        $user = User::findOrFail($id);
        
        $paidTransactions = Transaction::where('user_id', $user->id)->where('status', 'PAID')->get();
        $purchases = $paidTransactions->count();
        $totalSpent = $paidTransactions->sum('price');
        
        $usdSpent = 0;
        foreach($paidTransactions as $trx) {
            if (strtoupper($trx->payment_method) === 'STRIPE') {
                $usdSpent += ($trx->price / 100);
            } else {
                $usdSpent += ($trx->price / 16000); // Approx IDR to USD
            }
        }

        $availableTiers = [
            ['id' => 'bronze', 'name' => 'Bronze', 'color' => '#cd7f32', 'threshold' => 0],
            ['id' => 'silver', 'name' => 'Silver', 'color' => '#cbd5e1', 'threshold' => 50],
            ['id' => 'gold', 'name' => 'Gold', 'color' => '#f59e0b', 'threshold' => 200],
            ['id' => 'platinum', 'name' => 'Platinum', 'color' => '#d1d5db', 'threshold' => 1000],
            ['id' => 'diamond', 'name' => 'Diamond', 'color' => '#05d9e8', 'threshold' => 2500],
            ['id' => 'crown', 'name' => 'Crown', 'color' => '#ff2a6d', 'threshold' => 5000],
        ];

        $unlockedFrames = [];
        $tier = null;

        foreach($availableTiers as $t) {
            if ($usdSpent >= $t['threshold']) {
                $t['icon'] = asset('images/tiers/' . $t['id'] . '_badge.png');
                $unlockedFrames[] = $t;
                $tier = $t;
            }
        }

        if (!$tier) {
            $tier = $availableTiers[0];
            $tier['icon'] = asset('images/tiers/bronze_badge.png');
            $unlockedFrames[] = $tier;
        }

        $equippedFrame = null;
        if ($user->equipped_frame) {
            foreach($unlockedFrames as $f) {
                if ($f['id'] === $user->equipped_frame) {
                    $equippedFrame = $f;
                    break;
                }
            }
        }

        $transactions = Transaction::where('user_id', $user->id)->orderBy('created_at', 'desc')->paginate(10);
        
        return view('profile.show', compact('user', 'purchases', 'totalSpent', 'transactions', 'tier', 'usdSpent', 'unlockedFrames', 'equippedFrame'));
    }

    public function updateBanner(Request $request, $name, $id)
    {
        $user = User::findOrFail($id);

        // Only allow user to update their own banner, or admin
        if (auth()->id() !== $user->id && auth()->user()->role !== 'admin') {
            return back()->with('error', 'Unauthorized.');
        }

        if ($request->input('action') === 'delete') {
            if ($user->banner && file_exists(public_path($user->banner))) {
                @unlink(public_path($user->banner));
            }
            $user->banner = null;
            $user->save();
            return back()->with('success', 'Profile banner deleted successfully!');
        }

        $request->validate([
            'banner' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // Max 5MB
        ]);

        if ($request->hasFile('banner')) {
            $file = $request->file('banner');
            $filename = time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/banners'), $filename);

            // Optional: delete old banner if exists
            if ($user->banner && file_exists(public_path($user->banner))) {
                @unlink(public_path($user->banner));
            }

            $user->banner = 'uploads/banners/' . $filename;
            $user->save();

            return back()->with('success', 'Profile banner updated successfully!');
        }

        return back()->with('error', 'Failed to upload banner.');
    }

    public function updateBio(Request $request, $name, $id)
    {
        $user = User::findOrFail($id);

        if (auth()->id() !== $user->id && auth()->user()->role !== 'admin') {
            return back()->with('error', 'Unauthorized.');
        }

        $request->validate([
            'bio' => 'nullable|string|max:1000',
        ]);

        $bio = $request->bio ?? '';

        // 1. Strip all HTML tags completely
        $bio = strip_tags($bio);

        // 2. Strip any URLs (http, https, ftp, www)
        $bio = preg_replace(
            '/\b(https?:\/\/|ftp:\/\/|www\.)\S+/i',
            '[link removed]',
            $bio
        );

        // 3. Strip dangerous markdown-link patterns [text](url)
        $bio = preg_replace('/\[([^\]]*)\]\([^)]*\)/', '$1', $bio);

        // 4. Collapse excessive whitespace (more than 3 consecutive newlines → 2)
        $bio = preg_replace('/\n{3,}/', "\n\n", $bio);

        // 5. Trim
        $bio = trim($bio);

        $user->bio = $bio ?: null;
        $user->save();

        return back()->with('success', 'About section updated!');
    }

    public function updateAvatarAndFrame(Request $request, $name, $id)
    {
        $user = User::findOrFail($id);

        if (auth()->id() !== $user->id && auth()->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'avatar_base64' => 'nullable|string',
            'equipped_frame' => 'nullable|string'
        ]);

        if ($request->filled('avatar_base64')) {
            $base64_image = $request->input('avatar_base64');
            if (preg_match('/^data:image\/(\w+);base64,/', $base64_image, $type)) {
                $data = substr($base64_image, strpos($base64_image, ',') + 1);
                $data = base64_decode($data);
                if ($data !== false) {
                    $filename = time() . '_' . $user->id . '.png';
                    if (!file_exists(public_path('uploads/avatars'))) {
                        mkdir(public_path('uploads/avatars'), 0755, true);
                    }
                    file_put_contents(public_path('uploads/avatars/') . $filename, $data);
                    
                    // delete old avatar if it's local
                    if ($user->avatar && str_starts_with($user->avatar, '/uploads/avatars/') && file_exists(public_path($user->avatar))) {
                        @unlink(public_path($user->avatar));
                    }
                    
                    $user->avatar = '/uploads/avatars/' . $filename;
                }
            }
        }

        // if equipped_frame is 'none', we set it to null
        $frame = $request->input('equipped_frame');
        $user->equipped_frame = ($frame === 'none') ? null : $frame;
        $user->save();

        return response()->json(['success' => true, 'message' => 'Avatar and frame updated successfully!']);
    }
}
