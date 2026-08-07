<?php

namespace App\Http\Controllers;

use App\Models\Balita;
use App\Models\Peringatan;
use App\Models\Pengukuran;
use App\Models\Posyandu;
use App\Models\SesiPosyandu;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();
        $ids  = $user->accessiblePosyanduIds();
        $bulan = now()->startOfMonth();

        $bln = now()->month;
        $thn = now()->year;

        // Ambil pengukuran TERAKHIR per balita dalam bulan ini
        // subquery: id pengukuran terbaru per balita_id di bulan ini
        // Hanya hitung balita AKTIF untuk semua statistik bulan ini
        $latestPerBalita = Pengukuran::selectRaw('MAX(id) as id')
            ->whereHas('balita', fn($q) => $q->whereIn('posyandu_id', $ids)->where('aktif', true))
            ->whereMonth('tanggal_ukur', $bln)
            ->whereYear('tanggal_ukur', $thn)
            ->groupBy('balita_id');

        $snapshot = Pengukuran::whereIn('id', $latestPerBalita)->get();

        $statistik = [
            'total_balita'    => Balita::whereIn('posyandu_id', $ids)->where('aktif', true)->count(),
            'hadir_bulan_ini'  => $snapshot->count(),
            'belum_diukur'     => Balita::whereIn('posyandu_id', $ids)->where('aktif', true)
                                    ->whereNotIn('id', $snapshot->pluck('balita_id'))->count(),
            // Status gizi BB/U — Permenkes No. 2 Tahun 2020 (4 kategori)
            'gizi_buruk'    => $snapshot->where('status_gizi', 'GIZI_BURUK')->count(),
            'gizi_kurang'   => $snapshot->where('status_gizi', 'GIZI_KURANG')->count(),
            'gizi_baik'     => $snapshot->where('status_gizi', 'GIZI_BAIK')->count(),
            'risiko_lebih'  => $snapshot->where('status_gizi', 'RISIKO_LEBIH')->count(),
            // Status stunting TB/U — Permenkes No. 2 Tahun 2020
            'stunting_sangat_pendek' => $snapshot->where('status_stunting', 'SANGAT_PENDEK')->count(),
            'stunting_pendek'        => $snapshot->where('status_stunting', 'PENDEK')->count(),
            'stunting_normal'        => $snapshot->where('status_stunting', 'NORMAL')->count(),
            'stunting_tinggi'        => $snapshot->where('status_stunting', 'TINGGI')->count(),
            // Status wasting BB/TB — Permenkes No. 2 Tahun 2020
            'wasting_sangat_kurus'   => $snapshot->where('status_wasting', 'SANGAT_KURUS')->count(),
            'wasting_kurus'          => $snapshot->where('status_wasting', 'KURUS')->count(),
            'wasting_normal'         => $snapshot->where('status_wasting', 'NORMAL')->count(),
            'wasting_berisiko_gemuk' => $snapshot->where('status_wasting', 'BERISIKO_GEMUK')->count(),
            'wasting_gemuk'          => $snapshot->where('status_wasting', 'GEMUK')->count(),
            'obesitas_wasting'       => $snapshot->where('status_wasting', 'OBESITAS')->count(),
            // Kenaikan Berat Badan (KBB) — standar KMS Indonesia
            'kbb_naik'   => $snapshot->where('status_kbb', 'N')->count(),
            'kbb_kurang' => $snapshot->where('status_kbb', 'T')->count(),
            'kbb_turun'  => $snapshot->where('status_kbb', 'O')->count(),
            // Status IMT/U — Permenkes No. 2 Tahun 2020
            'imt_sangat_kurus'       => $snapshot->where('status_imt_u', 'SANGAT_KURUS')->count(),
            'imt_kurus'              => $snapshot->where('status_imt_u', 'KURUS')->count(),
            'imt_normal'             => $snapshot->where('status_imt_u', 'NORMAL')->count(),
            'imt_berisiko_gemuk'     => $snapshot->where('status_imt_u', 'BERISIKO_GEMUK')->count(),
            'imt_gemuk'              => $snapshot->where('status_imt_u', 'GEMUK')->count(),
            'imt_obesitas'           => $snapshot->where('status_imt_u', 'OBESITAS')->count(),
            // Double Burden (definisi WHO): Wasting + Stunting bersamaan
            'double_burden'          => $snapshot->whereIn('status_wasting', ['SANGAT_KURUS', 'KURUS'])
                                                  ->whereIn('status_stunting', ['PENDEK', 'SANGAT_PENDEK'])
                                                  ->count(),
            // EWS — dihitung dari flag_ews pengukuran TERAKHIR per balita
            'ews_merah'  => $snapshot->where('flag_ews', 'MERAH')->count(),
            'ews_kuning' => $snapshot->where('flag_ews', 'KUNING')->count(),
        ];

        $peringatanAktif = Peringatan::with(['balita.posyandu', 'pengukuran'])
            ->whereHas('balita', fn($q) => $q->whereIn('posyandu_id', $ids)->where('aktif', true))
            ->where('status_tindak_lanjut', 'BELUM')
            ->orderByRaw("CASE WHEN level_risiko='MERAH' THEN 0 WHEN level_risiko='KUNING' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($p) => [
                'id'               => $p->id,
                'level_risiko'     => $p->level_risiko,
                'jenis_peringatan' => $p->label_jenis,
                'pesan'            => $p->pesan,
                'created_at'       => $p->created_at->diffForHumans(),
                'balita_nama'      => $p->balita->nama,
                'balita_id'        => $p->balita_id,
                'posyandu_nama'    => $p->balita->posyandu->nama,
            ]);

        // Balita yang butuh perhatian khusus: stunting atau gizi buruk (pengukuran terakhir)
        // Subquery: ambil tanggal pengukuran terakhir per balita
        $latestTanggal = \App\Models\Pengukuran::selectRaw('MAX(tanggal_ukur) as tanggal_ukur, balita_id')
            ->groupBy('balita_id');

        $balitaPerhatian = Balita::whereIn('posyandu_id', $ids)
            ->where('aktif', true)
            ->whereHas('pengukuran', fn($q) =>
                $q->joinSub($latestTanggal, 'latest', fn($j) =>
                    $j->on('pengukuran.balita_id', '=', 'latest.balita_id')
                      ->on('pengukuran.tanggal_ukur', '=', 'latest.tanggal_ukur')
                )
                ->where(fn($q2) =>
                    $q2->whereIn('pengukuran.status_gizi', ['GIZI_BURUK', 'GIZI_KURANG'])
                       ->orWhereIn('pengukuran.status_stunting', ['PENDEK', 'SANGAT_PENDEK'])
                )
            )
            ->with(['posyandu:id,nama', 'pengukuran' => fn($q) =>
                $q->latest('tanggal_ukur')->limit(1)
                  ->select('id', 'balita_id', 'berat_badan_kg', 'tinggi_badan_cm',
                           'status_gizi', 'status_stunting', 'z_score_bb_u', 'z_score_tb_u',
                           'tanggal_ukur', 'flag_ews')
            ])
            ->orderBy('nama')
            ->get()
            ->map(fn($b) => [
                'id'             => $b->id,
                'nama'           => $b->nama,
                'jenis_kelamin'  => $b->jenis_kelamin,
                'posyandu'       => $b->posyandu->nama,
                'umur_bulan'     => $b->umur_bulan,
                'status_gizi'    => $b->pengukuran->first()?->status_gizi,
                'status_stunting'=> $b->pengukuran->first()?->status_stunting,
                'flag_ews'       => $b->pengukuran->first()?->flag_ews ?? 'KUNING',
                'tanggal_ukur'   => $b->pengukuran->first()?->tanggal_ukur?->format('d/m/Y'),
                'bb'             => $b->pengukuran->first()?->berat_badan_kg,
                'tb'             => $b->pengukuran->first()?->tinggi_badan_cm,
            ]);

        $sesiMendatang = SesiPosyandu::whereIn('posyandu_id', $ids)
            ->where('tanggal', '>=', today())
            ->where('status', '!=', 'SELESAI')
            ->with('posyandu:id,nama')
            ->orderBy('tanggal')
            ->limit(3)
            ->get()
            ->map(fn($s) => [
                'id'            => $s->id,
                'tanggal'       => $s->tanggal->translatedFormat('d F Y'),
                'hari'          => $s->tanggal->translatedFormat('l'),
                'tema'          => $s->tema,
                'posyandu_nama' => $s->posyandu->nama,
                'status'        => $s->status,
                'is_today'      => $s->tanggal->isToday(),
            ]);

        // Jadwal rutin posyandu (untuk kader: tampil jika belum ada sesi bulan ini)
        $jadwalRuntin = null;
        if ($user->role === 'kader') {
            $adaSesiBulanIni = SesiPosyandu::whereIn('posyandu_id', $ids)
                ->whereYear('tanggal', now()->year)
                ->whereMonth('tanggal', now()->month)
                ->exists();

            if (!$adaSesiBulanIni) {
                $jadwalRuntin = Posyandu::whereIn('id', $ids)
                    ->whereNotNull('jadwal_hari')
                    ->get(['id', 'nama', 'jadwal_minggu', 'jadwal_hari', 'jadwal_jam'])
                    ->map(fn($p) => [
                        'nama'          => $p->nama,
                        'jadwal_minggu' => $p->jadwal_minggu,
                        'jadwal_hari'   => $p->jadwal_hari,
                        'jadwal_jam'    => $p->jadwal_jam ? substr($p->jadwal_jam, 0, 5) : null,
                    ]);
            }
        }

        // Untuk Nakes: ringkasan per posyandu binaan
        $ringkasanPerPosyandu = null;
        if ($user->role === 'nakes' && count($ids) > 1) {
            $ringkasanPerPosyandu = Posyandu::whereIn('id', $ids)->orderBy('nama')->get()
                ->map(fn($p) => [
                    'id'           => $p->id,
                    'nama'         => $p->nama,
                    'total_balita' => Balita::where('posyandu_id', $p->id)->where('aktif', true)->count(),
                    'ews_aktif'    => Peringatan::whereHas('balita', fn($q) => $q->where('posyandu_id', $p->id))
                                        ->whereIn('status_tindak_lanjut', ['BELUM','DILAPORKAN','DALAM_PROSES'])
                                        ->count(),
                    'ews_merah'    => Peringatan::whereHas('balita', fn($q) => $q->where('posyandu_id', $p->id))
                                        ->where('level_risiko', 'MERAH')
                                        ->whereIn('status_tindak_lanjut', ['BELUM','DILAPORKAN','DALAM_PROSES'])
                                        ->count(),
                    'stunting'     => Balita::where('posyandu_id', $p->id)->where('aktif', true)
                                        ->whereHas('pengukuran', fn($q) =>
                                            $q->whereIn('status_stunting', ['PENDEK','SANGAT_PENDEK'])
                                              ->whereRaw('id = (SELECT MAX(id) FROM pengukuran p2 WHERE p2.balita_id = pengukuran.balita_id)')
                                        )->count(),
                ]);
        }

        // Usulan nonaktif menunggu tindakan user ini
        $usulanMenunggu = null;
        if (in_array($user->role, ['nakes', 'petugas', 'admin'])) {
            $statusTarget = $user->role === 'nakes' ? 'DIUSULKAN' : 'DITERUSKAN';
            $usulanMenunggu = \App\Models\UsulanNonaktif::with(['balita.posyandu', 'pengusul:id,nama'])
                ->where('status', $statusTarget)
                ->whereHas('balita', fn($q) => $q->whereIn('posyandu_id', $ids))
                ->latest()
                ->get()
                ->map(fn($u) => [
                    'id'          => $u->id,
                    'balita_id'   => $u->balita_id,
                    'balita_nama' => $u->balita->nama,
                    'posyandu'    => $u->balita->posyandu->nama,
                    'alasan'      => $u->alasan,
                    'pengusul'    => $u->pengusul->nama,
                    'tanggal'     => $u->created_at->diffForHumans(),
                ]);
        }

        return Inertia::render('Dashboard/Index', compact('statistik', 'peringatanAktif', 'balitaPerhatian', 'sesiMendatang', 'ringkasanPerPosyandu', 'usulanMenunggu', 'jadwalRuntin'));
    }

    public function admin(): Response
    {
        $latestIds = \App\Models\Pengukuran::selectRaw('MAX(id) as id')
            ->groupBy('balita_id')->pluck('id');

        $snapshot = \App\Models\Pengukuran::whereIn('id', $latestIds)
            ->whereHas('balita', fn($q) => $q->where('aktif', true))
            ->get();

        $totalBalita = Balita::where('aktif', true)->count();

        // Ringkasan sistem
        $sistem = [
            'total_posyandu'  => Posyandu::where('aktif', true)->count(),
            'total_balita'    => $totalBalita,
            'balita_nonaktif' => Balita::where('aktif', false)->count(),
            'total_kader'     => \App\Models\User::where('role', 'kader')->count(),
            'total_nakes'     => \App\Models\User::where('role', 'nakes')->count(),
            'total_petugas'   => \App\Models\User::where('role', 'petugas')->count(),
            'total_dinas'     => \App\Models\User::where('role', 'dinas')->count(),
        ];

        // Statistik kesehatan
        $statistik = [
            'total_balita'           => $totalBalita,
            'gizi_buruk'             => $snapshot->where('status_gizi', 'GIZI_BURUK')->count(),
            'gizi_kurang'            => $snapshot->where('status_gizi', 'GIZI_KURANG')->count(),
            'gizi_baik'              => $snapshot->where('status_gizi', 'GIZI_BAIK')->count(),
            'risiko_lebih'           => $snapshot->where('status_gizi', 'RISIKO_LEBIH')->count(),
            'stunting_sangat_pendek' => $snapshot->where('status_stunting', 'SANGAT_PENDEK')->count(),
            'stunting_pendek'        => $snapshot->where('status_stunting', 'PENDEK')->count(),
            'stunting_normal'        => $snapshot->where('status_stunting', 'NORMAL')->count(),
            'stunting_tinggi'        => $snapshot->where('status_stunting', 'TINGGI')->count(),
            'ews_merah'              => Peringatan::where('level_risiko', 'MERAH')
                                        ->whereIn('status_tindak_lanjut', ['BELUM','DILAPORKAN','DALAM_PROSES'])->count(),
            'ews_kuning'             => Peringatan::where('level_risiko', 'KUNING')
                                        ->whereIn('status_tindak_lanjut', ['BELUM','DILAPORKAN','DALAM_PROSES'])->count(),
        ];

        $stuntingTotal = $statistik['stunting_sangat_pendek'] + $statistik['stunting_pendek'];
        $statistik['pct_stunting'] = $totalBalita > 0 ? round($stuntingTotal / $totalBalita * 100, 1) : 0;
        $statistik['stunting']     = $stuntingTotal;

        // Akun terbaru (10 terakhir)
        $akunTerbaru = \App\Models\User::whereIn('role', ['kader','nakes','petugas','dinas'])
            ->latest()->limit(10)
            ->get(['id','nama','role','email','created_at'])
            ->map(fn($u) => [
                'id'         => $u->id,
                'nama'       => $u->nama,
                'role'       => $u->role,
                'email'      => $u->email,
                'created_at' => $u->created_at->diffForHumans(),
            ]);

        // Pengukuran terbaru (10 terakhir)
        $pengukuranTerbaru = \App\Models\Pengukuran::with(['balita.posyandu', 'pencatat'])
            ->latest('tanggal_ukur')->limit(10)->get()
            ->map(fn($p) => [
                'balita_nama'   => $p->balita->nama,
                'posyandu_nama' => $p->balita->posyandu->nama,
                'tanggal_ukur'  => $p->tanggal_ukur->format('d/m/Y'),
                'pencatat'      => $p->pencatat?->nama ?? '-',
                'flag_ews'      => $p->flag_ews,
            ]);

        // EWS terbaru belum ditangani
        $ewsTerbaru = Peringatan::with(['balita.posyandu'])
            ->whereIn('status_tindak_lanjut', ['BELUM','DILAPORKAN'])
            ->orderByRaw("CASE WHEN level_risiko='MERAH' THEN 0 ELSE 1 END")
            ->latest()->limit(8)->get()
            ->map(fn($p) => [
                'balita_nama'   => $p->balita->nama,
                'posyandu_nama' => $p->balita->posyandu->nama,
                'level_risiko'  => $p->level_risiko,
                'status'        => $p->status_tindak_lanjut,
                'created_at'    => $p->created_at->diffForHumans(),
            ]);

        return Inertia::render('Dashboard/Admin', compact(
            'sistem', 'statistik', 'akunTerbaru', 'pengukuranTerbaru', 'ewsTerbaru'
        ));
    }

    public function dinas(): Response
    {
        $posyandu = Posyandu::where('aktif', true)->with('wilayah')->get();

        // Snapshot pengukuran terakhir per balita untuk akurasi
        $latestIds = \App\Models\Pengukuran::selectRaw('MAX(id) as id')
            ->groupBy('balita_id')->pluck('id');

        $ringkasan = $posyandu->map(function ($p) use ($latestIds) {
            $totalBalita = Balita::where('posyandu_id', $p->id)->where('aktif', true)->count();

            $snapshot = \App\Models\Pengukuran::whereIn('id', $latestIds)
                ->whereHas('balita', fn($q) => $q->where('posyandu_id', $p->id)->where('aktif', true))
                ->get();

            $stunting    = $snapshot->whereIn('status_stunting', ['PENDEK', 'SANGAT_PENDEK'])->count();
            $giziKurang  = $snapshot->whereIn('status_gizi', ['GIZI_BURUK', 'GIZI_KURANG'])->count();
            $diukurBulan = \App\Models\Pengukuran::whereHas('balita', fn($q) =>
                                $q->where('posyandu_id', $p->id)->where('aktif', true))
                            ->whereMonth('tanggal_ukur', now()->month)
                            ->whereYear('tanggal_ukur', now()->year)
                            ->distinct('balita_id')->count();
            $ewsMerah    = Peringatan::whereHas('balita', fn($q) => $q->where('posyandu_id', $p->id))
                            ->where('level_risiko', 'MERAH')
                            ->whereIn('status_tindak_lanjut', ['BELUM', 'DILAPORKAN', 'DALAM_PROSES'])
                            ->count();

            return [
                'id'            => $p->id,
                'nama'          => $p->nama,
                'wilayah'       => $p->wilayah?->nama ?? '-',
                'total_balita'  => $totalBalita,
                'stunting'      => $stunting,
                'pct_stunting'  => $totalBalita > 0 ? round($stunting / $totalBalita * 100) : 0,
                'gizi_kurang'   => $giziKurang,
                'pct_gizi_kurang' => $totalBalita > 0 ? round($giziKurang / $totalBalita * 100) : 0,
                'diukur_bulan'  => $diukurBulan,
                'pct_cakupan'   => $totalBalita > 0 ? round($diukurBulan / $totalBalita * 100) : 0,
                'ews_merah'     => $ewsMerah,
            ];
        });

        // Total balita aktif sebagai denominator tetap
        $totalBalitaAktif = \App\Models\Balita::where('aktif', true)->count();

        // Tren stunting 6 bulan — denominator: total balita aktif (konsisten dengan kartu atas)
        $tren = collect(range(5, 0))->map(function ($i) use ($totalBalitaAktif) {
            $bulan = now()->subMonths($i);

            // Ambil pengukuran terbaru per balita yang diukur s/d akhir bulan tersebut
            $latestSdBulan = \App\Models\Pengukuran::selectRaw('MAX(id) as id')
                ->where('tanggal_ukur', '<=', $bulan->endOfMonth())
                ->groupBy('balita_id')->pluck('id');

            $stunting = \App\Models\Pengukuran::whereIn('id', $latestSdBulan)
                ->whereHas('balita', fn($q) => $q->where('aktif', true))
                ->whereIn('status_stunting', ['PENDEK', 'SANGAT_PENDEK'])->count();

            return [
                'bulan'        => $bulan->translatedFormat('M Y'),
                'stunting'     => $stunting,
                'total'        => $totalBalitaAktif,
                'pct_stunting' => $totalBalitaAktif > 0
                    ? round($stunting / $totalBalitaAktif * 100, 1) : 0,
            ];
        });

        // Snapshot semua balita aktif untuk agregat gizi & stunting
        $snapshotNasional = \App\Models\Pengukuran::whereIn('id', $latestIds)
            ->whereHas('balita', fn($q) => $q->where('aktif', true))
            ->get();

        $totalBalita = $ringkasan->sum('total_balita');

        $totalNasional = [
            'total_balita'   => $totalBalita,
            'stunting'       => $ringkasan->sum('stunting'),
            'pct_stunting'   => $totalBalita > 0
                ? round($ringkasan->sum('stunting') / $totalBalita * 100, 1) : 0,
            'gizi_kurang'    => $ringkasan->sum('gizi_kurang'),
            'ews_merah'      => $ringkasan->sum('ews_merah'),
            'total_posyandu' => $posyandu->count(),
            // Status gizi agregat
            'gizi_buruk'     => $snapshotNasional->where('status_gizi', 'GIZI_BURUK')->count(),
            'gizi_kurang_n'  => $snapshotNasional->where('status_gizi', 'GIZI_KURANG')->count(),
            'gizi_baik'      => $snapshotNasional->where('status_gizi', 'GIZI_BAIK')->count(),
            'risiko_lebih'   => $snapshotNasional->where('status_gizi', 'RISIKO_LEBIH')->count(),
            'hadir_diukur'   => $snapshotNasional->count(),
            // Status stunting agregat
            'stunting_sangat_pendek' => $snapshotNasional->where('status_stunting', 'SANGAT_PENDEK')->count(),
            'stunting_pendek'        => $snapshotNasional->where('status_stunting', 'PENDEK')->count(),
            'stunting_normal'        => $snapshotNasional->where('status_stunting', 'NORMAL')->count(),
            'stunting_tinggi'        => $snapshotNasional->where('status_stunting', 'TINGGI')->count(),
        ];

        return Inertia::render('Dashboard/Dinas', compact('ringkasan', 'totalNasional', 'tren'));
    }
}
