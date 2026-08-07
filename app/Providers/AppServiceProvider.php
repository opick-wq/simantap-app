<?php

namespace App\Providers;

use App\Models\Pengukuran;
use App\Observers\PengukuranObserver;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Daftarkan observer — EWS berjalan otomatis saat pengukuran disimpan
        Pengukuran::observe(PengukuranObserver::class);

        // Bagikan data global ke semua halaman Inertia
        Inertia::share([
            'auth' => fn() => [
                'user' => Auth::user() ? [
                    'id'         => Auth::user()->id,
                    'nama'       => Auth::user()->nama,
                    'email'      => Auth::user()->email,
                    'role'       => Auth::user()->role,
                    'posyandu_id'=> Auth::user()->posyandu_id,
                ] : null,
            ],
            'ewsCount' => fn() => Auth::check()
                ? \App\Models\Peringatan::whereHas('balita', fn($q) =>
                    $q->whereIn('posyandu_id', Auth::user()->accessiblePosyanduIds()))
                  ->where('status_tindak_lanjut', 'BELUM')->count()
                : 0,
            'flash' => fn() => [
                'success' => session('success'),
                'error'   => session('error'),
            ],
        ]);
    }
}
