<?php

namespace App\Http\Controllers;

use App\Models\Balita;
use App\Models\UsulanNonaktif;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UsulanNonaktifController extends Controller
{
    /** Kader: ajukan usulan nonaktif */
    public function store(Request $request, Balita $balita)
    {
        $user = Auth::user();
        abort_unless($user->role === 'kader', 403);
        abort_unless(in_array($balita->posyandu_id, $user->accessiblePosyanduIds()), 403);

        // Cegah duplikat usulan aktif
        $adaAktif = $balita->usulanNonaktif()
            ->whereIn('status', ['DIUSULKAN', 'DITERUSKAN'])
            ->exists();
        abort_if($adaAktif, 422, 'Sudah ada usulan aktif untuk balita ini.');

        $data = $request->validate([
            'alasan' => 'required|string|max:500',
        ]);

        $usulan = UsulanNonaktif::create([
            'balita_id'   => $balita->id,
            'pengusul_id' => $user->id,
            'alasan'      => $data['alasan'],
            'status'      => 'DIUSULKAN',
        ]);

        return back()->with('success', 'Usulan nonaktif berhasil diajukan. Menunggu persetujuan Nakes.');
    }

    /** Nakes: teruskan ke Petugas atau tolak */
    public function tindakLanjutNakes(Request $request, UsulanNonaktif $usulan)
    {
        $user = Auth::user();
        abort_unless(in_array($user->role, ['nakes', 'admin']), 403);
        abort_unless($usulan->status === 'DIUSULKAN', 422, 'Usulan sudah diproses.');

        $data = $request->validate([
            'aksi'         => 'required|in:TERUSKAN,TOLAK',
            'catatan_nakes'=> 'nullable|string|max:500',
        ]);

        $statusBaru = $data['aksi'] === 'TERUSKAN' ? 'DITERUSKAN' : 'DITOLAK';

        $usulan->update([
            'status'        => $statusBaru,
            'nakes_id'      => $user->id,
            'catatan_nakes' => $data['catatan_nakes'],
        ]);

        $pesan = $statusBaru === 'DITERUSKAN'
            ? 'Usulan diteruskan ke Petugas Puskesmas.'
            : 'Usulan ditolak.';

        return back()->with('success', $pesan);
    }

    /** Petugas: setujui (nonaktif/hapus) atau tolak */
    public function keputusanPetugas(Request $request, UsulanNonaktif $usulan)
    {
        $user = Auth::user();
        abort_unless(in_array($user->role, ['petugas', 'admin']), 403);
        abort_unless($usulan->status === 'DITERUSKAN', 422, 'Usulan belum diteruskan Nakes.');

        $data = $request->validate([
            'aksi'            => 'required|in:SETUJUI_NONAKTIF,SETUJUI_HAPUS,TOLAK',
            'catatan_petugas' => 'nullable|string|max:500',
        ]);

        if ($data['aksi'] === 'TOLAK') {
            $usulan->update([
                'status'           => 'DITOLAK',
                'petugas_id'       => $user->id,
                'catatan_petugas'  => $data['catatan_petugas'],
            ]);
            return back()->with('success', 'Usulan ditolak.');
        }

        $tindakan = $data['aksi'] === 'SETUJUI_HAPUS' ? 'HAPUS' : 'NONAKTIF';

        $usulan->update([
            'status'          => 'DISETUJUI',
            'petugas_id'      => $user->id,
            'catatan_petugas' => $data['catatan_petugas'],
            'tindakan_akhir'  => $tindakan,
        ]);

        $balita = $usulan->balita;

        if ($tindakan === 'HAPUS') {
            $balita->delete();
            return redirect()->route('balita.index')
                ->with('success', "Data balita {$balita->nama} telah dihapus permanen.");
        }

        // Nonaktifkan
        $balita->update([
            'aktif'            => false,
            'tanggal_nonaktif' => today(),
            'alasan_nonaktif'  => $usulan->alasan,
        ]);

        return back()->with('success', "Balita {$balita->nama} berhasil dinonaktifkan.");
    }
}
