<?php

namespace App\Http\Controllers;

use App\Models\Balita;
use App\Models\SesiPosyandu;
use App\Models\WhoZscoreReference;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class OrangTuaController extends Controller
{
    public function dashboard(): Response
    {
        $user   = Auth::user();
        $balita = Balita::where('user_id_ortu', $user->id)
            ->with(['pengukuran' => fn($q) => $q->latest('tanggal_ukur')->limit(1)])
            ->aktif()
            ->get()
            ->map(fn($b) => [
                'id'                => $b->id,
                'nama'              => $b->nama,
                'umur_lengkap'      => $b->umur_lengkap,
                'jenis_kelamin'     => $b->jenis_kelamin,
                'pengukuran_terakhir' => $b->pengukuran->first() ? [
                    'tanggal_ukur'   => $b->pengukuran->first()->tanggal_ukur->format('d M Y'),
                    'berat_badan_kg' => $b->pengukuran->first()->berat_badan_kg,
                    'tinggi_badan_cm'=> $b->pengukuran->first()->tinggi_badan_cm,
                    'status_gizi'    => $b->pengukuran->first()->status_gizi,
                    'flag_ews'       => $b->pengukuran->first()->flag_ews,
                ] : null,
            ]);

        $posyanduIds = Balita::where('user_id_ortu', $user->id)->aktif()->pluck('posyandu_id');

        $sesiMendatang = SesiPosyandu::whereIn('posyandu_id', $posyanduIds)
            ->where('tanggal', '>=', now()->toDateString())
            ->where('status', '!=', 'SELESAI')
            ->with('posyandu')
            ->orderBy('tanggal')
            ->take(3)
            ->get()
            ->map(fn($s) => [
                'id'            => $s->id,
                'tanggal'       => $s->tanggal->translatedFormat('d F Y'),
                'hari'          => $s->tanggal->translatedFormat('l'),
                'tema'          => $s->tema,
                'posyandu_nama' => $s->posyandu->nama,
                'status'        => $s->status,
                'sisa_hari'     => (int) now()->startOfDay()->diffInDays($s->tanggal, false),
            ]);

        // Jadwal rutin posyandu (fallback jika belum ada sesi terjadwal)
        $posyandus = \App\Models\Posyandu::whereIn('id', $posyanduIds)
            ->get(['id', 'nama', 'jadwal_hari', 'jadwal_jam', 'alamat']);

        return Inertia::render('OrangTua/Dashboard', compact('balita', 'sesiMendatang', 'posyandus'));
    }

    public function showAnak(Balita $balita): Response
    {
        // Pastikan balita ini memang milik orang tua yang login
        abort_if($balita->user_id_ortu !== Auth::id(), 403);

        $riwayat = $balita->pengukuran()
            ->orderBy('tanggal_ukur', 'asc')
            ->get()
            ->map(fn($p) => [
                'tanggal_ukur'    => $p->tanggal_ukur->format('d M Y'),
                'umur_bulan'      => $p->umur_bulan,
                'berat_badan_kg'  => $p->berat_badan_kg,
                'tinggi_badan_cm' => $p->tinggi_badan_cm,
                'z_score_bb_u'    => $p->z_score_bb_u,
                'z_score_tb_u'    => $p->z_score_tb_u,
                'status_gizi'     => $p->status_gizi,
                'status_stunting' => $p->status_stunting,
                'flag_ews'        => $p->flag_ews,
            ]);

        $balitaData = [
            'id'            => $balita->id,
            'nama'          => $balita->nama,
            'tanggal_lahir' => $balita->tanggal_lahir->format('d M Y'),
            'umur_lengkap'  => $balita->umur_lengkap,
            'jenis_kelamin' => $balita->jenis_kelamin,
            'nama_ibu'      => $balita->nama_ibu,
            'posyandu'      => $balita->posyandu->nama,
        ];

        $gender   = $balita->jenis_kelamin;
        $curveBbU  = WhoZscoreReference::getCurveData('BB_U', $gender);
        $curveTbU  = WhoZscoreReference::getCurveData('TB_U', $gender);
        $curveBbTb = WhoZscoreReference::getCurveData('BB_TB', $gender);
        $curveImtU = WhoZscoreReference::getCurveData('IMT_U', $gender);

        return Inertia::render('OrangTua/AnakShow', [
            'balita'    => $balitaData,
            'riwayat'   => $riwayat,
            'curveBbU'  => $curveBbU,
            'curveTbU'  => $curveTbU,
            'curveBbTb' => $curveBbTb,
            'curveImtU' => $curveImtU,
        ]);
    }

    public function jadwal(): Response
    {
        $user    = Auth::user();
        $balita  = Balita::where('user_id_ortu', $user->id)->aktif()->first();
        $jadwal  = null;

        if ($balita) {
            $jadwal = [
                'posyandu_nama' => $balita->posyandu->nama,
                'jadwal_hari'   => $balita->posyandu->jadwal_hari,
                'jadwal_jam'    => $balita->posyandu->jadwal_jam,
                'alamat'        => $balita->posyandu->alamat,
            ];
        }

        return Inertia::render('OrangTua/Jadwal', compact('jadwal'));
    }
}
