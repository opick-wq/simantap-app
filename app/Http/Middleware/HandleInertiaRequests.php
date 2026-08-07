<?php

namespace App\Http\Middleware;

use App\Models\Peringatan;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Data yang di-share ke semua halaman Inertia (tersedia via $page.props).
     */
    public function share(Request $request): array
    {
        $user     = $request->user();
        $ewsCount = 0;

        if ($user && in_array($user->role, ['kader', 'nakes', 'petugas', 'admin'])) {
            $ids = $user->accessiblePosyanduIds();
            $query = Peringatan::whereHas('balita', fn($q) => $q->whereIn('posyandu_id', $ids))
                ->where('level_risiko', 'MERAH');

            if ($user->role === 'nakes') {
                // Nakes: hitung peringatan MERAH yang sudah dilaporkan kader & menunggu tindakan
                $query->where('status_tindak_lanjut', 'DILAPORKAN');
            } else {
                $query->where('status_tindak_lanjut', 'BELUM');
            }

            $ewsCount = $query->count();
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id'          => $user->id,
                    'nama'        => $user->nama,
                    'email'       => $user->email,
                    'role'        => $user->role,
                    'posyandu_id'   => $user->posyandu_id,
                    'posyandu_nama' => $user->posyandu_id
                        ? \App\Models\Posyandu::find($user->posyandu_id)?->nama
                        : null,
                ] : null,
            ],
            'ewsCount' => $ewsCount,
            'flash'    => [
                'success'      => $request->session()->get('success'),
                'error'        => $request->session()->get('error'),
                'importResult' => $request->session()->get('importResult'),
            ],
        ];
    }
}
