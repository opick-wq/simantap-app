<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Izinkan akses hanya jika role user ada di daftar yang diperbolehkan.
     * Contoh pemakaian di route: middleware('role:kader,petugas,admin')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, $roles)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akses ditolak.'], 403);
            }
            // Redirect ke dashboard sesuai role, jangan abort 403
            if ($user) {
                return match($user->role) {
                    'admin'     => redirect()->route('admin.dashboard'),
                    'dinas'     => redirect()->route('dinas.dashboard'),
                    'orang_tua' => redirect()->route('ortu.dashboard'),
                    default     => redirect()->route('dashboard'),
                };
            }
            return redirect('/login');
        }

        return $next($request);
    }
}
