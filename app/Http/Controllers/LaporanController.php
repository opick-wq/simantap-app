<?php

namespace App\Http\Controllers;

use App\Models\Balita;
use App\Models\Pengukuran;
use App\Models\Peringatan;
use App\Models\TindakLanjut;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index(): Response
    {
        $ids = Auth::user()->accessiblePosyanduIds();

        $ringkasan = [
            'total_balita'   => Balita::whereIn('posyandu_id', $ids)->where('aktif', true)->count(),
            'gizi_buruk'     => $this->countStatus($ids, 'status_gizi', 'GIZI_BURUK'),
            'gizi_kurang'    => $this->countStatus($ids, 'status_gizi', 'GIZI_KURANG'),
            'gizi_baik'      => $this->countStatus($ids, 'status_gizi', 'GIZI_BAIK'),
            'stunting'       => $this->countStatus($ids, 'status_stunting', 'PENDEK') +
                                $this->countStatus($ids, 'status_stunting', 'SANGAT_PENDEK'),
            'ews_bulan_ini'  => Peringatan::whereHas('balita', fn($q) => $q->whereIn('posyandu_id', $ids))
                                          ->where('status_tindak_lanjut', 'BELUM')
                                          ->whereMonth('created_at', now()->month)
                                          ->whereYear('created_at', now()->year)
                                          ->count(),
            'ews_menunggak'  => Peringatan::whereHas('balita', fn($q) => $q->whereIn('posyandu_id', $ids))
                                          ->where('status_tindak_lanjut', 'BELUM')
                                          ->where(fn($q) => $q
                                              ->whereMonth('created_at', '!=', now()->month)
                                              ->orWhereYear('created_at', '!=', now()->year))
                                          ->count(),
        ];

        return Inertia::render('Laporan/Index', compact('ringkasan'));
    }

    public function exportJson(Request $request)
    {
        $ids = Auth::user()->accessiblePosyanduIds();
        
        // FITUR BARU: Menggunakan rentang dari dan sampai[cite: 1]
        $dari = $request->input('dari', now()->startOfMonth()->format('Y-m'));
        $sampai = $request->input('sampai', now()->endOfMonth()->format('Y-m'));

        $tanggalMulai = $dari . '-01';
        $tanggalSelesai = Carbon::parse($sampai . '-01')->endOfMonth()->format('Y-m-d');

        $data = Pengukuran::with(['balita.posyandu'])
            ->whereHas('balita', fn($q) => $q->whereIn('posyandu_id', $ids))
            ->whereBetween('tanggal_ukur', [$tanggalMulai, $tanggalSelesai]) // Menggunakan whereBetween[cite: 1]
            ->get()
            ->map(fn($p) => [
                'nama_balita'    => $p->balita->nama,
                'jenis_kelamin'  => $p->balita->jenis_kelamin,
                'umur_bulan'     => $p->umur_saat_ukur,
                'posyandu'       => $p->balita->posyandu->nama,
                'tanggal_ukur'   => $p->tanggal_ukur->format('Y-m-d'),
                'berat_badan_kg' => $p->berat_badan_kg,
                'tinggi_badan_cm'=> $p->tinggi_badan_cm,
                'z_score_bb_u'   => $p->z_score_bb_u,
                'z_score_tb_u'   => $p->z_score_tb_u,
                'status_gizi'    => $p->status_gizi,
                'status_stunting'=> $p->status_stunting,
                'flag_ews'       => $p->flag_ews,
            ]);

        // FITUR BARU: Nama file otomatis sesuai rentang[cite: 1]
        return response()->json($data)
            ->header('Content-Disposition', "attachment; filename=laporan-{$dari}_sd_{$sampai}.json");
    }

    public function exportPdf(Request $request)
    {
        // PDF tetap dibiarkan per bulan[cite: 1]
        $ids   = Auth::user()->accessiblePosyanduIds();
        $bulan = $request->input('bulan', now()->format('Y-m'));
        [$year, $month] = explode('-', $bulan);

        $pengukuranData = Pengukuran::with(['balita.posyandu'])
            ->whereHas('balita', fn($q) => $q->whereIn('posyandu_id', $ids)->where('aktif', true))
            ->whereYear('tanggal_ukur', $year)
            ->whereMonth('tanggal_ukur', $month)
            ->orderBy('tanggal_ukur')
            ->get()
            ->map(fn($p) => [
                'nama_balita'     => $p->balita->nama,
                'jenis_kelamin'   => $p->balita->jenis_kelamin,
                'umur_bulan'      => $p->umur_bulan,
                'posyandu'        => $p->balita->posyandu->nama,
                'tanggal_ukur'    => $p->tanggal_ukur->format('d/m/Y'),
                'berat_badan_kg'  => $p->berat_badan_kg,
                'tinggi_badan_cm' => $p->tinggi_badan_cm,
                'z_score_bb_u'    => $p->z_score_bb_u,
                'z_score_tb_u'    => $p->z_score_tb_u,
                'status_gizi'     => $p->status_gizi,
                'status_stunting' => $p->status_stunting,
                'flag_ews'        => $p->flag_ews ?? 'HIJAU',
            ])->toArray();

        $peringatanData = Peringatan::with(['balita.posyandu'])
            ->whereHas('balita', fn($q) => $q->whereIn('posyandu_id', $ids))
            ->where('status_tindak_lanjut', 'BELUM')
            ->orderByRaw("CASE WHEN level_risiko='MERAH' THEN 0 ELSE 1 END")
            ->get()
            ->map(fn($p) => [
                'balita_nama'  => $p->balita->nama,
                'posyandu_nama'=> $p->balita->posyandu->nama,
                'level'        => $p->level_risiko,
                'jenis'        => $p->label_jenis,
                'pesan'        => $p->pesan,
            ])->toArray();

        // Snapshot: pengukuran terakhir per balita AKTIF bulan ini
        $latestIds = Pengukuran::selectRaw('MAX(id) as id')
            ->whereHas('balita', fn($q) => $q->whereIn('posyandu_id', $ids)->where('aktif', true))
            ->whereYear('tanggal_ukur', $year)->whereMonth('tanggal_ukur', $month)
            ->groupBy('balita_id');
        $snapshot = Pengukuran::whereIn('id', $latestIds)->get();

        $totalBalita  = Balita::whereIn('posyandu_id', $ids)->where('aktif', true)->count();
        $belumDiukur  = Balita::whereIn('posyandu_id', $ids)->where('aktif', true)
                            ->whereNotIn('id', $snapshot->pluck('balita_id'))->count();

        $ringkasan = [
            'total_balita'         => $totalBalita,
            'hadir'                => $snapshot->count(),
            'belum_diukur'         => $belumDiukur,
            'gizi_buruk'           => $snapshot->where('status_gizi', 'GIZI_BURUK')->count(),
            'gizi_kurang'          => $snapshot->where('status_gizi', 'GIZI_KURANG')->count(),
            'gizi_baik'            => $snapshot->where('status_gizi', 'GIZI_BAIK')->count(),
            'berisiko_lebih'       => $snapshot->where('status_gizi', 'RISIKO_LEBIH')->count(),
            'gizi_lebih'           => 0,
            'stunting_sangat_pendek' => $snapshot->where('status_stunting', 'SANGAT_PENDEK')->count(),
            'stunting_pendek'      => $snapshot->where('status_stunting', 'PENDEK')->count(),
            'stunting_normal'      => $snapshot->where('status_stunting', 'NORMAL')->count(),
            'stunting_tinggi'      => $snapshot->where('status_stunting', 'TINGGI')->count(),
            'double_burden'        => $snapshot->whereIn('status_wasting', ['SANGAT_KURUS','KURUS'])
                                               ->whereIn('status_stunting', ['PENDEK','SANGAT_PENDEK'])->count(),
            'ews_merah'            => Peringatan::whereHas('balita', fn($q) => $q->whereIn('posyandu_id', $ids))
                                        ->where('level_risiko', 'MERAH')->where('status_tindak_lanjut', 'BELUM')->count(),
            'ews_kuning'           => Peringatan::whereHas('balita', fn($q) => $q->whereIn('posyandu_id', $ids))
                                        ->where('level_risiko', 'KUNING')->where('status_tindak_lanjut', 'BELUM')->count(),
            'ews_aktif'            => Peringatan::whereHas('balita', fn($q) => $q->whereIn('posyandu_id', $ids))
                                        ->where('status_tindak_lanjut', 'BELUM')->count(),
        ];

        $intervData = TindakLanjut::with(['balita:id,nama', 'pencatat:id,nama', 'peringatan:id,jenis_peringatan,level_risiko'])
            ->whereHas('balita', fn($q) => $q->whereIn('posyandu_id', $ids))
            ->whereYear('created_at', $year)->whereMonth('created_at', $month)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($t) => [
                'balita_nama'          => $t->balita?->nama ?? '-',
                'pencatat'             => $t->pencatat?->nama ?? '-',
                'tanggal'              => $t->created_at->format('d/m/Y'),
                'jenis_tindakan'       => $t->jenis_tindakan ?? [],
                'dilaporkan_ke_atasan' => $t->dilaporkan_ke_atasan,
                'catatan'              => $t->catatan,
                'status_akhir'         => $t->status_akhir,
                'peringatan_jenis'     => $t->peringatan?->jenis_peringatan,
                'peringatan_level'     => $t->peringatan?->level_risiko,
            ])->toArray();

        $labelTindakan = [
            'tambahan_gizi' => 'Tambahan Gizi',
            'edukasi'       => 'Edukasi Gizi & Pola Asuh',
            'rujuk'         => 'Rujuk ke Faskes',
            'konsultasi'    => 'Konsultasi Nakes',
            'lainnya'       => 'Lainnya',
        ];

        $namaBulan = ['', 'Januari','Februari','Maret','April','Mei','Juni',
                      'Juli','Agustus','September','Oktober','November','Desember'];

        $pdf = Pdf::loadView('pdf.laporan', [
            'data'          => $pengukuranData,
            'peringatan'    => $peringatanData,
            'intervensi'    => $intervData,
            'labelTindakan' => $labelTindakan,
            'ringkasan'     => $ringkasan,
            'bulanLabel'    => $namaBulan[(int)$month] . ' ' . $year,
            'dicetak'       => now()->format('d/m/Y'),
            'operator'      => Auth::user()->nama,
            'posyanduList'  => \App\Models\Posyandu::whereIn('id', $ids)->pluck('nama')->toArray(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download("laporan-{$bulan}.pdf");
    }

    public function exportExcel(Request $request)
    {
        $ids = Auth::user()->accessiblePosyanduIds();
        
        // FITUR BARU: Menggunakan rentang dari dan sampai[cite: 1]
        $dari = $request->input('dari', now()->startOfMonth()->format('Y-m'));
        $sampai = $request->input('sampai', now()->endOfMonth()->format('Y-m'));

        $tanggalMulai = $dari . '-01';
        $tanggalSelesai = Carbon::parse($sampai . '-01')->endOfMonth()->format('Y-m-d');

        $data = Pengukuran::with(['balita.posyandu'])
            ->whereHas('balita', fn($q) => $q->whereIn('posyandu_id', $ids)->where('aktif', true))
            ->whereBetween('tanggal_ukur', [$tanggalMulai, $tanggalSelesai]) // Menggunakan whereBetween[cite: 1]
            ->get();

        $csv = "No,NIK Balita,Nama Balita,Nama Ibu,Jenis Kelamin,Umur (bln),Posyandu,Tanggal Ukur,BB (kg),TB (cm),Status Gizi,Status Stunting,Flag EWS\n";
        foreach ($data as $i => $p) {
            $umur = $p->umur_bulan
                ?? (int) $p->balita->tanggal_lahir->diffInMonths($p->tanggal_ukur);
            $csv .= implode(',', [
                $i + 1,
                $p->balita->nik_balita ?? '',
                "\"{$p->balita->nama}\"",
                "\"{$p->balita->nama_ibu}\"",
                $p->balita->jenis_kelamin,
                $umur,
                "\"{$p->balita->posyandu->nama}\"",
                $p->tanggal_ukur->format('Y-m-d'),
                $p->berat_badan_kg,
                $p->tinggi_badan_cm,
                $p->status_gizi ?? '',
                $p->status_stunting ?? '',
                $p->flag_ews,
            ]) . "\n";
        }

        // FITUR BARU: Nama file otomatis sesuai rentang[cite: 1]
        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=laporan-{$dari}_sd_{$sampai}.csv",
        ]);
    }

    private function countStatus(array $ids, string $col, string $val): int
    {
        return Pengukuran::whereHas('balita', fn($q) => $q->whereIn('posyandu_id', $ids))
            ->where($col, $val)
            ->whereMonth('tanggal_ukur', now()->month)
            ->count();
    }
}