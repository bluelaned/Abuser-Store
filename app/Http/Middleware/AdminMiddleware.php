<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Cek apakah user sudah login?
        // 2. Cek apakah kolom role isinya 'admin'?
        if (auth()->check() && auth()->user()->role === 'admin') {
            return $next($request);
        }

        // Kalau bukan admin, tendang ke halaman home dengan pesan error
        return redirect('/')->with('error', 'Woi! Lu bukan admin, jangan coba-coba masuk sini!');
    }
}