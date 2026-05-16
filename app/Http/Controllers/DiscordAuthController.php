<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; 
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http; 

class DiscordAuthController extends Controller
{
    public function redirect(Request $request)
    {
        if ($request->has('redirect_id')) {
            $request->session()->put('checkout_product_id', $request->redirect_id);
            $request->session()->save(); 
        }
        
        return Socialite::driver('discord')->redirect();
    }

    public function callback(Request $request)
    {
        try {
            $userDiscord = Socialite::driver('discord')->user();

            // 1. CARI USER BERDASARKAN PROVIDER_ID
            $user = User::where('provider_id', $userDiscord->getId())->first();

            // Cek apakah user adalah owner utama berdasarkan Discord ID yang mutlak (jangan dari email karena bisa dipalsukan jika unverified)
            $isAdmin = ($userDiscord->getId() === '397108454210273280');
            $role = $isAdmin ? 'admin' : 'user';

            if (!$user) {
                $email = $userDiscord->getEmail() ?? 'discord_' . $userDiscord->getId() . '@abuser.com';

                // 2. KALAU BELUM ADA (PENDAFTAR BARU), BUAT BARU
                $user = User::create([
                    'name' => $userDiscord->getName() ?? $userDiscord->getNickname(),
                    'email' => $email, 
                    'provider_id' => $userDiscord->getId(),
                    'provider_name' => 'discord',
                    'discord_id' => $userDiscord->getId(),
                    'password' => bcrypt(Str::random(16)),
                    'avatar' => $userDiscord->getAvatar(),
                    'role' => $role,
                ]);
            } else {
                // 3. KALAU SUDAH ADA, UPDATE FOTO, NAMA, SAMA DISCORD_ID
                $updateData = [
                    'name' => $userDiscord->getName() ?? $userDiscord->getNickname(),
                    'avatar' => $userDiscord->getAvatar(),
                    'discord_id' => $userDiscord->getId(),
                ];
                
                // Mencegah Role Downgrade (Jika dia sudah admin di DB, jangan diturunkan jadi user)
                if ($isAdmin) {
                    $updateData['role'] = 'admin';
                }
                
                $user->update($updateData);
            }

            // === LOGIC TRACKING IP, LOKASI, DEVICE & ISP DIMULAI ===
            $ip = $request->ip();
            $userAgent = $request->header('User-Agent');
            
            // Deteksi OS & Tipe Device
            $os = 'Unknown OS';
            $device = '💻 Desktop';
            if (preg_match('/windows/i', $userAgent)) { $os = 'Windows'; }
            elseif (preg_match('/macintosh|mac os x/i', $userAgent)) { $os = 'Mac OS'; }
            elseif (preg_match('/linux/i', $userAgent)) { $os = 'Linux'; }
            elseif (preg_match('/android/i', $userAgent)) { $os = 'Android'; $device = '📱 Mobile'; }
            elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) { $os = 'iOS'; $device = '📱 Mobile'; }

            // Deteksi Browser
            $browser = 'Unknown Browser';
            if (preg_match('/OPR|Opera/i', $userAgent)) { $browser = 'Opera'; }
            elseif (preg_match('/Edg/i', $userAgent)) { $browser = 'Edge'; }
            elseif (preg_match('/Chrome/i', $userAgent)) { $browser = 'Chrome'; }
            elseif (preg_match('/Safari/i', $userAgent)) { $browser = 'Safari'; }
            elseif (preg_match('/Firefox/i', $userAgent)) { $browser = 'Firefox'; }

            // Pakai IP testing untuk localhost
            if ($ip == '127.0.0.1' || $ip == '::1') {
                $ip = '103.154.148.83'; 
            }

            try {
                // Tarik data dari API gratis
                $geoResponse = Http::timeout(3)->get("http://ip-api.com/json/{$ip}");
                $geoData = $geoResponse->json();

                if (isset($geoData['status']) && $geoData['status'] === 'success') {
                    $user->update([
                        'last_ip' => $ip,
                        'location_city' => $geoData['city'],
                        'location_state' => $geoData['regionName'],
                        'location_country' => $geoData['country'],
                        'location_flag' => 'https://flagcdn.com/w20/' . strtolower($geoData['countryCode']) . '.png',
                        'isp' => $geoData['isp'] ?? 'Unknown ISP',
                        'os' => $os,
                        'browser' => $browser,
                        'device_type' => $device,
                    ]);
                } else {
                    // Kalau gagal dapat lokasi, tetep simpan info devicenya
                    $user->update(['last_ip' => $ip, 'os' => $os, 'browser' => $browser, 'device_type' => $device]);
                }
            } catch (\Exception $e) {
                // Kalau API down, tetep simpan info devicenya
                $user->update(['last_ip' => $ip, 'os' => $os, 'browser' => $browser, 'device_type' => $device]);
            }
            // === LOGIC TRACKING SELESAI ===

            // Login dan sisa logic-nya
            Auth::login($user, true);
            $request->session()->regenerate();

            if ($request->session()->has('checkout_product_id')) {
                $productId = $request->session()->pull('checkout_product_id');
                return redirect()->route('checkout', $productId);
            }

            return redirect('/'); 

        } catch (\Exception $e) {
            return "Error Login: " . $e->getMessage();
        }
    }

    public function saveEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email,' . Auth::id(),
        ]);

        $user = Auth::user();
        $user->email = $request->email;
        $user->save();

        return back()->with('success', 'Email berhasil disimpan!');
    }
}
