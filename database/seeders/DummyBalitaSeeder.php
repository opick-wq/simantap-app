<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seed 15 balita dummy dengan variasi status gizi, usia, dan skenario EWS.
 * Data ini digunakan untuk alpha testing dan pelatihan kader.
 */
class DummyBalitaSeeder extends Seeder
{
    public function run(): void
    {
        $posyandu = DB::table('posyandu')->orderBy('id')->get();
        $kaderId  = DB::table('users')->where('role', 'kader')->first()->id;
        $ortuId   = DB::table('users')->where('role', 'orang_tua')->first()->id;

        // [nama, ibu, usia_bulan, jk, skenario_gizi, posyandu_index (0-3)]
        // Apel=0 (15), Mangga=1 (14), Anggur=2 (13), Jeruk=3 (12)
        $daftarBalita = [
            // ── Posyandu Apel (15 balita) ──────────────────────────────
            ['Anisa Putri',      'Rina Sari',      18, 'P', 'NORMAL',          0],
            ['Budi Santoso',     'Wati Dewi',      24, 'L', 'NORMAL',          0],
            ['Citra Lestari',    'Sari Indah',      9, 'P', 'NORMAL',          0],
            ['Doni Pratama',     'Yuni Ayu',       36, 'L', 'NORMAL',          0],
            ['Eva Rahayu',       'Maya Putri',     12, 'P', 'GIZI_KURANG',     0],
            ['Fajar Nugroho',    'Lina Dewi',      48, 'L', 'GIZI_KURANG',     0],
            ['Gita Melati',      'Sri Mulyani',     6, 'P', 'GIZI_BURUK',      0],
            ['Hendra Wijaya',    'Novi Susanti',   30, 'L', 'GIZI_BURUK',      0],
            ['Indah Kusuma',     'Tini Rahayu',    15, 'P', 'STUNTING',        0],
            ['Joko Susilo',      'Erni Wati',      42, 'L', 'STUNTING',        0],
            ['Kartika Sari',     'Rini Putri',     21, 'P', 'WEIGHT_FALTERING',0],
            ['Lukman Hakim',     'Desi Ayu',        3, 'L', 'WEIGHT_FALTERING',0],
            ['Melati Indah',     'Nani Sari',      54, 'P', 'NORMAL',          0],
            ['Naufal Rahman',    'Siti Aisyah',    60, 'L', 'GIZI_LEBIH',      0],
            ['Olivia Dewi',      'Ratna Sari',      8, 'P', 'ABSEN',           0],
            // ── Posyandu Mangga (14 balita) ────────────────────────────
            ['Putri Ayu',        'Dewi Lestari',   20, 'P', 'NORMAL',          1],
            ['Rizky Pratama',    'Sari Wulan',     33, 'L', 'NORMAL',          1],
            ['Siti Aminah',      'Yanti Dewi',     11, 'P', 'NORMAL',          1],
            ['Taufik Hidayat',   'Nurul Huda',     45, 'L', 'NORMAL',          1],
            ['Uci Rahayu',       'Lia Susanti',    16, 'P', 'GIZI_KURANG',     1],
            ['Vino Saputra',     'Mira Agustina',  28, 'L', 'GIZI_KURANG',     1],
            ['Wulan Dewi',       'Fitri Handayani', 7, 'P', 'GIZI_BURUK',      1],
            ['Xander Putra',     'Hana Pertiwi',   37, 'L', 'STUNTING',        1],
            ['Yuni Astuti',      'Rini Oktavia',   14, 'P', 'STUNTING',        1],
            ['Zaki Ramadhan',    'Dina Permata',   50, 'L', 'NORMAL',          1],
            ['Amel Cahyani',     'Susi Rahayu',    22, 'P', 'WEIGHT_FALTERING',1],
            ['Bagas Setiawan',   'Evi Nurlaela',    5, 'L', 'NORMAL',          1],
            ['Cika Permata',     'Lena Juliani',   40, 'P', 'GIZI_LEBIH',      1],
            ['Dimas Ardiansyah', 'Wati Susilawati',58, 'L', 'NORMAL',          1],
            // ── Posyandu Anggur (13 balita) ────────────────────────────
            ['Elsa Febriani',    'Rina Kusuma',    19, 'P', 'NORMAL',          2],
            ['Farhan Maulana',   'Tini Wahyuni',   31, 'L', 'NORMAL',          2],
            ['Gilang Ramadan',   'Novi Rahayu',    10, 'L', 'NORMAL',          2],
            ['Hana Safitri',     'Yuli Astuti',    44, 'P', 'GIZI_KURANG',     2],
            ['Ilham Saputra',    'Dewi Nuraeni',   17, 'L', 'GIZI_BURUK',      2],
            ['Jasmine Putri',    'Sri Handayani',  26, 'P', 'STUNTING',        2],
            ['Kevin Adiputra',   'Lina Marlina',    4, 'L', 'NORMAL',          2],
            ['Liana Sari',       'Maya Sari',      55, 'P', 'NORMAL',          2],
            ['Muhamad Fauzi',    'Ratu Permata',   38, 'L', 'WEIGHT_FALTERING',2],
            ['Nayla Putri',      'Sinta Dewi',     13, 'P', 'NORMAL',          2],
            ['Omar Saputra',     'Fitri Lestari',  47, 'L', 'STUNTING',        2],
            ['Prita Cahyani',    'Neng Suryani',   23, 'P', 'GIZI_LEBIH',      2],
            ['Qori Ramadhani',   'Erni Susilawati',60, 'L', 'NORMAL',          2],
            // ── Posyandu Jeruk (12 balita) ─────────────────────────────
            ['Rani Agustina',    'Dede Sumiati',   25, 'P', 'NORMAL',          3],
            ['Satria Wibowo',    'Susi Ratnasari', 32, 'L', 'NORMAL',          3],
            ['Tia Amelia',       'Yayah Komariah',  8, 'P', 'NORMAL',          3],
            ['Umar Fauzi',       'Nani Rohaeni',   43, 'L', 'GIZI_KURANG',     3],
            ['Vira Handayani',   'Lilis Suryani',  16, 'P', 'GIZI_BURUK',      3],
            ['Wahyu Nugroho',    'Euis Fitriani',  29, 'L', 'STUNTING',        3],
            ['Xenia Maharani',   'Teti Rosmawati',  6, 'P', 'NORMAL',          3],
            ['Yoga Pratama',     'Ai Nurhayati',   52, 'L', 'NORMAL',          3],
            ['Zahra Aulia',      'Cucu Sukaesih',  18, 'P', 'WEIGHT_FALTERING',3],
            ['Aldi Maulana',     'Eneng Sumiati',  35, 'L', 'NORMAL',          3],
            ['Bella Permata',    'Icih Rahayu',    11, 'P', 'GIZI_LEBIH',      3],
            ['Candra Kusuma',    'Nining Nurlaela', 48, 'L', 'NORMAL',         3],
        ];

        foreach ($daftarBalita as $idx => [$nama, $namaIbu, $usiaBulan, $jk, $skenario, $pIdx]) {
            $posyanduId = $posyandu[$pIdx]->id ?? $posyandu[0]->id;
            $tglLahir   = Carbon::now()->subMonths($usiaBulan)->subDays(rand(1, 20));
            $linkOrtu   = ($idx === 0) ? $ortuId : null;

            $balitaId = DB::table('balita')->insertGetId([
                'posyandu_id'   => $posyanduId,
                'nik_balita'    => '327304' . str_pad($idx + 1, 10, '0', STR_PAD_LEFT),
                'nama'          => $nama,
                'tanggal_lahir' => $tglLahir->format('Y-m-d'),
                'jenis_kelamin' => $jk,
                'nama_ibu'      => $namaIbu,
                'alamat'        => 'Jl. Contoh No. ' . ($idx + 1) . ', RW 0' . ($pIdx + 1),
                'user_id_ortu'  => $linkOrtu,
                'aktif'         => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            $this->buatPengukuran($balitaId, $kaderId, $tglLahir, $usiaBulan, $jk, $skenario);
        }

        $this->command->info('✅ 54 balita dummy (Apel:15, Mangga:14, Anggur:13, Jeruk:12) berhasil dibuat.');
    }

    private function buatPengukuran(
        int $balitaId, int $kaderId,
        Carbon $tglLahir, int $usiaSaatIni, string $jk, string $skenario
    ): void {
        // Buat 3 titik pengukuran: 2 bulan lalu, 1 bulan lalu, hari ini
        $pengukuranList = [
            ['bulan_lalu' => 2, 'bb_delta' => 0.5,  'tb_delta' => 1.5],
            ['bulan_lalu' => 1, 'bb_delta' => 0.4,  'tb_delta' => 1.0],
            ['bulan_lalu' => 0, 'bb_delta' => 0.0,  'tb_delta' => 0.0],
        ];

        // BB dan TB dasar berdasarkan usia dan status gizi
        [$bbBase, $tbBase] = $this->getBaseAntropometri($usiaSaatIni, $jk, $skenario);

        // Untuk skenario ABSEN: hanya buat 1 pengukuran lama
        if ($skenario === 'ABSEN') {
            $tglUkur = Carbon::now()->subMonths(4);
            DB::table('pengukuran')->insert([
                'balita_id'       => $balitaId,
                'dicatat_oleh'    => $kaderId,
                'tanggal_ukur'    => $tglUkur->format('Y-m-d'),
                'umur_bulan'      => max(0, $usiaSaatIni - 4),
                'berat_badan_kg'  => $bbBase - 0.5,
                'tinggi_badan_cm' => $tbBase - 2.0,
                'z_score_bb_u'    => -1.2,
                'z_score_tb_u'    => -1.0,
                'status_gizi'     => 'GIZI_KURANG',
                'status_stunting' => 'NORMAL',
                'flag_ews'        => 'KUNING',
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
            return;
        }

        $bbAkumulasi = $bbBase - 0.9; // Mulai dari lebih kecil
        $tbAkumulasi = $tbBase - 2.5;

        foreach ($pengukuranList as $i => $p) {
            $bbAkumulasi += ($skenario === 'WEIGHT_FALTERING' && $i === 2)
                ? -0.2   // Skenario: BB turun di pengukuran terakhir
                : $p['bb_delta'];
            $tbAkumulasi += $p['tb_delta'];

            $tglUkur = Carbon::now()->subMonths($p['bulan_lalu']);
            $umur    = max(0, $usiaSaatIni - $p['bulan_lalu']);

            [$zBbU, $zTbU, $statusGizi, $statusStunting] =
                $this->hitungStatusFiktif($bbAkumulasi, $tbAkumulasi, $skenario, $i);

            DB::table('pengukuran')->insert([
                'balita_id'       => $balitaId,
                'dicatat_oleh'    => $kaderId,
                'tanggal_ukur'    => $tglUkur->format('Y-m-d'),
                'umur_bulan'      => $umur,
                'berat_badan_kg'  => round($bbAkumulasi, 2),
                'tinggi_badan_cm' => round($tbAkumulasi, 1),
                'z_score_bb_u'    => $zBbU,
                'z_score_tb_u'    => $zTbU,
                'status_gizi'     => $statusGizi,
                'status_stunting' => $statusStunting,
                'flag_ews'        => $this->flagEws($zBbU, $zTbU, $skenario, $i),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
    }

    private function getBaseAntropometri(int $usia, string $jk, string $skenario): array
    {
        // Nilai median kasar berdasarkan usia (sumber: WHO median tables)
        $median = [
            3 => [6.0, 61.0], 6 => [7.9, 67.0], 8 => [8.8, 70.0],
            9 => [9.2, 72.0], 12 => [10.2, 76.0], 15 => [11.0, 79.0],
            18 => [11.5, 82.0], 21 => [12.0, 85.0], 24 => [12.5, 88.0],
            30 => [13.5, 92.0], 36 => [14.5, 96.0], 42 => [15.5, 100.0],
            48 => [16.5, 104.0], 54 => [17.5, 107.0], 60 => [18.5, 110.0],
        ];

        $keys  = array_keys($median);
        usort($keys, fn($a, $b) => abs($a - $usia) - abs($b - $usia));
        $key   = $keys[0] ?? 12;
        [$bb, $tb] = $median[$key] ?? [10.0, 75.0];

        // Offset berdasarkan skenario
        return match($skenario) {
            'GIZI_BURUK'       => [$bb * 0.68, $tb * 0.94],
            'GIZI_KURANG'      => [$bb * 0.82, $tb * 0.97],
            'STUNTING'         => [$bb * 0.90, $tb * 0.88],
            'WEIGHT_FALTERING' => [$bb * 0.88, $tb * 0.97],
            'GIZI_LEBIH'       => [$bb * 1.20, $tb * 1.02],
            default            => [$bb, $tb],
        };
    }

    private function hitungStatusFiktif(float $bb, float $tb, string $skenario, int $idx): array
    {
        return match($skenario) {
            'GIZI_BURUK'  => [-3.5, -2.0, 'GIZI_BURUK',  'PENDEK'],
            'GIZI_KURANG' => [-2.2, -1.5, 'GIZI_KURANG', 'NORMAL'],
            'STUNTING'    => [-1.5, -2.8, 'GIZI_BAIK',   'PENDEK'],
            'WEIGHT_FALTERING' => $idx === 2
                ? [-2.5, -1.0, 'GIZI_KURANG', 'NORMAL']
                : [-1.5, -1.0, 'GIZI_BAIK',   'NORMAL'],
            'GIZI_LEBIH'  => [2.5, 1.0, 'GIZI_LEBIH', 'NORMAL'],
            default       => [round(rand(-10, 10) / 10, 1), round(rand(-5, 15) / 10, 1), 'GIZI_BAIK', 'NORMAL'],
        };
    }

    private function flagEws(float $zBbU, float $zTbU, string $skenario, int $idx): string
    {
        if ($skenario === 'GIZI_BURUK') return 'MERAH';
        if ($skenario === 'WEIGHT_FALTERING' && $idx === 2) return 'MERAH';
        if ($skenario === 'STUNTING') return 'MERAH';
        if ($skenario === 'GIZI_KURANG') return $idx === 2 ? 'KUNING' : 'HIJAU';
        return 'HIJAU';
    }
}
