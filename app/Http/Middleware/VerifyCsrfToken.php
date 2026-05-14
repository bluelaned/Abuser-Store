<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Masukkan route callback di sini agar tidak diblokir Laravel
        'callback/midtrans', 
        'callback/tripay', // Kalau nanti pakai Tripay juga
        'auth/steam/callback',
        'auth/steam',
    ];
}
