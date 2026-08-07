<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPosyanduAccess
{
    /**
     * Pastikan kader hanya bisa mengakses data Posyandu miliknya sendiri.
     * Admin, petugas, dan dinas bisa mengakses semua.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect('/login');
        }

        // Orang tua diarahkan ke portal khusus, bukan dashboard utama
        if ($user->role === 'orang_tua' && !$request->is('ortu*') && !$request->is('logout')) {
            return redirect('/ortu/dashboard');
        }

        return $next($request);
    }
}
