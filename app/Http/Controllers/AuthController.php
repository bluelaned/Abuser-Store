<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Fix Error: Menampilkan halaman login supaya gak "Method does not exist"
    public function login()
    {
        return view('auth.login');
    }

    // Proses login manual Admin
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/admin/dashboard'); // Sesuaikan rute dashboard lo
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ]);
    }
    // 1. Redirect ke Steam
    public function redirectToSteam()
    {
        return Socialite::driver('steam')->redirect();
    }

    // 2. Callback dari Steam
    public function handleSteamCallback()
    {
        try {
            $steamUser = Socialite::driver('steam')->user();
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Gagal login Steam.');
        }

        // Cari user berdasarkan Steam ID
        $user = User::where('steam_id', $steamUser->id)->first();

        if (!$user) {
            // BUAT USER BARU SEBAGAI 'USER' (BUKAN ADMIN)
            $user = User::create([
                'name' => $steamUser->nickname,
                'steam_id' => $steamUser->id,
                'avatar' => $steamUser->avatar,
                'email' => null, // Biarkan kosong supaya pop-up input email muncul
                'password' => null, 
                'role' => 'user', // Pastikan ini 'user'
            ]);
        }

        // Login dan paksa refresh session
        Auth::login($user, true);
        session()->regenerate();

        $productId = session('checkout_product_id');
        return $productId ? redirect()->route('checkout', $productId) : redirect('/');
    }

    // 3. Simpan Email Manual (Ini yang bikin input email ijo di gambar lo)
    public function saveEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email,' . Auth::id()
        ]);

        $user = User::find(Auth::id());
        $user->email = $request->email;
        $user->save();

        return back()->with('success', 'Email berhasil disimpan!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}