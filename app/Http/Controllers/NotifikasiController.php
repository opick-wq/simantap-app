<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    public function unread(): JsonResponse
    {
        $notif = Notifikasi::with(['peringatan.balita'])
            ->where('user_id', Auth::id())
            ->where('sudah_dibaca', false)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn($n) => [
                'id'          => $n->id,
                'pesan'       => $n->peringatan?->pesan ?? '-',
                'level'       => $n->peringatan?->level_risiko ?? 'HIJAU',
                'balita_nama' => $n->peringatan?->balita?->nama,
                'created_at'  => $n->created_at?->diffForHumans(),
            ]);

        return response()->json([
            'count' => $notif->count(),
            'items' => $notif,
        ]);
    }

    public function markRead(Notifikasi $notifikasi)
    {
        abort_unless($notifikasi->user_id === Auth::id(), 403);
        $notifikasi->update(['sudah_dibaca' => true, 'dibaca_pada' => now()]);
        return response()->json(['ok' => true]);
    }
}
