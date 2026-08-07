<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Buat 6 titik pengukuran bulanan (6 bulan terakhir) untuk semua balita aktif.
 * Pengukuran lama dihapus dulu agar tidak duplikat.
 */
class PengukuranDummySeeder extends Seeder
{
    // Median WHO kasar berdasarkan usia (bulan) → [bb_kg, tb_cm]
    private array $median = [
        3  => [6.0, 61.0],  6  => [7.9, 67.0],  8  => [8.8, 70.0],
        9  => [9.2, 72.0],  12 => [10.2, 76.0], 15 => [11.0, 79.0],
        18 => [11.5, 82.0], 21 => [12.0, 85.0], 24 => [12.5, 88.0],
        30 => [13.5, 92.0], 36 => [14.5, 96.0], 42 => [15.5, 100.0],
        48 => [16.5, 104.0],54 => [17.5, 107.0],60 => [18.5, 110.0],
    ];

    public function run(): void
    {
        $kaderId = DB::table('users')->where('role', 'kader')->first()->id;
        $balita  = DB::table('balita')->where('aktif', true)->get();

        // Hapus semua pengukuran lama
        DB::table('pengukuran')->whereIn('balita_id', $balita->pluck('id'))->delete();

        $total = 0;
        foreach ($balita as $b) {
            $tglLahir   = Carbon::parse($b->tanggal_lahir);
            $usiaSaatIni = (int) $tglLahir->diffInMonths(now());
            $skenario   = $this->tebakSkenario($b->nama);

            for ($i = 5; $i >= 0; $i--) {
                $tglUkur = Carbon::now()->startOfMonth()->subMonths($i);
                $umur    = max(1, (int) $tglLahir->diffInMonths($tglUkur));

                // Lewati jika balita belum lahir saat itu
                if ($tglUkur->lt($tglLahir)) continue;

                [$bb, $tb, $zBbU, $zTbU, $statusGizi, $statusStunting, $statusWasting, $ews] =
                    $this->hitungData($umur, $b->jenis_kelamin, $skenario, $i);

                DB::table('pengukuran')->insert([
                    'balita_id'        => $b->id,
                    'dicatat_oleh'     => $kaderId,
                    'tanggal_ukur'     => $tglUkur->format('Y-m-d'),
                    'umur_bulan'       => $umur,
                    'berat_badan_kg'   => $bb,
                    'tinggi_badan_cm'  => $tb,
                    'z_score_bb_u'     => $zBbU,
                    'z_score_tb_u'     => $zTbU,
                    'status_gizi'      => $statusGizi,
                    'status_stunting'  => $statusStunting,
                    'status_wasting'   => $statusWasting,
                    'flag_ews'         => $ews,
                    'created_at'       => $tglUkur,
                    'updated_at'       => $tglUkur,
                ]);
                $total++;
            }
        }

        $this->command->info("✅ {$total} data pengukuran dummy (6 bulan) berhasil dibuat untuk {$balita->count()} balita.");
    }

    private function tebakSkenario(string $nama): string
    {
        // Map berdasarkan nama dari DummyBalitaSeeder
        return match(true) {
            str_contains($nama, 'Gita') || str_contains($nama, 'Hendra') => 'GIZI_BURUK',
            str_contains($nama, 'Eva')  || str_contains($nama, 'Fajar')  => 'GIZI_KURANG',
            str_contains($nama, 'Indah')|| str_contains($nama, 'Joko')   => 'STUNTING',
            str_contains($nama, 'Kartika') || str_contains($nama, 'Lukman') => 'WEIGHT_FALTERING',
            str_contains($nama, 'Naufal') => 'GIZI_LEBIH',
            str_contains($nama, 'Olivia') => 'ABSEN',
            default => 'NORMAL',
        };
    }

    private function hitungData(int $umur, string $jk, string $skenario, int $bulanLalu): array
    {
        // Cari median terdekat
        $keys = array_keys($this->median);
        usort($keys, fn($a, $b) => abs($a - $umur) - abs($b - $umur));
        [$bbMedian, $tbMedian] = $this->median[$keys[0]] ?? [10.0, 76.0];

        // Offset skenario
        [$bbFaktor, $tbFaktor] = match($skenario) {
            'GIZI_BURUK'       => [0.68, 0.94],
            'GIZI_KURANG'      => [0.82, 0.97],
            'STUNTING'         => [0.90, 0.88],
            'WEIGHT_FALTERING' => [0.88, 0.97],
            'GIZI_LEBIH'       => [1.20, 1.02],
            default            => [1.00, 1.00],
        };

        // Tren realistis: makin ke bulan terbaru makin besar
        $progBb = ($bulanLalu === 0) ? 0 : -($bulanLalu * 0.25);
        $progTb = ($bulanLalu === 0) ? 0 : -($bulanLalu * 0.6);

        // Weight faltering: BB turun di 2 bulan terakhir
        if ($skenario === 'WEIGHT_FALTERING' && $bulanLalu <= 1) {
            $progBb -= 0.3;
        }

        $bb = round($bbMedian * $bbFaktor + $progBb + (rand(-3, 3) / 10), 2);
        $tb = round($tbMedian * $tbFaktor + $progTb + (rand(-2, 2) / 10), 1);

        // Z-score dan status
        [$zBbU, $zTbU, $statusGizi, $statusStunting, $statusWasting, $ews] = match($skenario) {
            'GIZI_BURUK'  => [-3.5, -2.2, 'GIZI_BURUK',  'PENDEK',       'SANGAT_KURUS', 'MERAH'],
            'GIZI_KURANG' => [-2.3, -1.2, 'GIZI_KURANG', 'NORMAL',       'KURUS',        $bulanLalu <= 1 ? 'KUNING' : 'HIJAU'],
            'STUNTING'    => [-1.2, -2.9, 'GIZI_BAIK',   'SANGAT_PENDEK','NORMAL',       'MERAH'],
            'WEIGHT_FALTERING' => $bulanLalu <= 1
                ? [-2.4, -1.0, 'GIZI_KURANG', 'NORMAL', 'KURUS',   'MERAH']
                : [-1.3, -1.0, 'GIZI_BAIK',   'NORMAL', 'NORMAL',  'HIJAU'],
            'GIZI_LEBIH'  => [2.4,  1.1, 'GIZI_LEBIH',  'NORMAL',       'GEMUK',        'KUNING'],
            'ABSEN'       => [-1.0, -0.8, 'GIZI_BAIK',  'NORMAL',       'NORMAL',       'HIJAU'],
            default       => [
                round(rand(-8, 12) / 10, 1),
                round(rand(-5, 15) / 10, 1),
                'GIZI_BAIK', 'NORMAL', 'NORMAL', 'HIJAU',
            ],
        };

        return [$bb, $tb, $zBbU, $zTbU, $statusGizi, $statusStunting, $statusWasting, $ews];
    }
}
