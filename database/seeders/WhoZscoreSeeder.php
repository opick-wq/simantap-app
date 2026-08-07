<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seed data referensi Z-score WHO Child Growth Standards 2006.
 *
 * CATATAN PENTING untuk mahasiswa developer:
 * File ini hanya berisi SAMPEL data (usia 0, 12, 24, 36, 48, 60 bulan).
 * Untuk data LENGKAP (0-60 bulan), unduh file CSV resmi dari:
 *   BB/U : https://www.who.int/tools/child-growth-standards/standards/weight-for-age
 *   TB/U : https://www.who.int/tools/child-growth-standards/standards/length-height-for-age
 *   BB/TB: https://www.who.int/tools/child-growth-standards/standards/weight-for-length-height
 *
 * Kemudian jalankan: php artisan db:seed --class=WhoZscoreFullSeeder
 * (setelah file CSV diletakkan di database/data/)
 */
class WhoZscoreSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('who_zscore_reference')->truncate();

        $data = [];

        // ── BB/U LAKI-LAKI (sampel key milestones) ────────────────────────
        $bbULaki = [
            // [umur, L, M, S, -3SD, -2SD, -1SD, median, +1SD, +2SD, +3SD]
            [0,  0.3487, 3.3464, 0.14602, 2.1, 2.5, 2.9, 3.3, 3.9, 4.4, 5.0],
            [1,  0.2297, 4.4709, 0.13395, 2.9, 3.4, 3.9, 4.5, 5.1, 5.8, 6.6],
            [2,  0.1970, 5.5675, 0.12385, 3.8, 4.3, 4.9, 5.6, 6.3, 7.1, 8.0],
            [3,  0.1738, 6.3762, 0.11727, 4.4, 5.0, 5.7, 6.4, 7.2, 8.0, 9.0],
            [4,  0.1553, 7.0023, 0.11316, 4.9, 5.6, 6.2, 7.0, 7.8, 8.7, 9.7],
            [5,  0.1395, 7.5105, 0.10988, 5.3, 6.0, 6.7, 7.5, 8.4, 9.3, 10.4],
            [6,  0.1257, 7.9340, 0.10760, 5.7, 6.4, 7.1, 7.9, 8.8, 9.8, 10.9],
            [9,  0.0776, 9.1649, 0.10579, 6.7, 7.5, 8.3, 9.2, 10.1, 11.2, 12.3],
            [12, 0.0490, 10.1540, 0.10730, 7.7, 8.6, 9.4, 10.2, 11.1, 12.1, 13.2],
            [18, 0.0000, 11.5534, 0.11260, 8.8, 9.8, 10.7, 11.6, 12.5, 13.5, 14.6],
            [24, 0.0000, 12.7359, 0.11690, 9.7, 10.8, 11.7, 12.7, 13.7, 14.8, 16.1],
            [36, 0.0000, 14.5200, 0.12520, 11.0, 12.2, 13.3, 14.5, 15.7, 17.1, 18.6],
            [48, 0.0000, 16.3036, 0.13400, 12.0, 13.4, 14.8, 16.3, 17.9, 19.7, 21.8],
            [60, 0.0000, 18.3267, 0.14320, 13.3, 14.9, 16.5, 18.3, 20.4, 22.7, 25.3],
        ];

        foreach ($bbULaki as $row) {
            $data[] = ['jenis_kelamin' => 'L', 'indikator' => 'BB_U',
                       'parameter' => $row[0], 'l_value' => $row[1],
                       'm_value' => $row[2], 's_value' => $row[3],
                       'sd_minus3' => $row[4], 'sd_minus2' => $row[5],
                       'sd_minus1' => $row[6], 'median_val' => $row[7],
                       'sd_plus1' => $row[8], 'sd_plus2' => $row[9], 'sd_plus3' => $row[10]];
        }

        // ── BB/U PEREMPUAN (sampel) ────────────────────────────────────────
        $bbUPerempuan = [
            [0,  0.3809, 3.2322, 0.14171, 2.0, 2.4, 2.8, 3.2, 3.7, 4.2, 4.8],
            [1,  0.1714, 4.1873, 0.13724, 2.7, 3.2, 3.6, 4.2, 4.8, 5.5, 6.2],
            [2,  0.1714, 5.1282, 0.13000, 3.4, 3.9, 4.5, 5.1, 5.8, 6.6, 7.5],
            [3,  0.1714, 5.8458, 0.12590, 4.0, 4.5, 5.2, 5.8, 6.6, 7.5, 8.5],
            [6,  0.1714, 7.2972, 0.11967, 5.4, 6.1, 6.7, 7.3, 8.1, 9.0, 10.2],
            [12, 0.1714, 9.5307, 0.11990, 7.1, 8.0, 8.8, 9.5, 10.4, 11.5, 12.8],
            [18, 0.1714, 10.8910, 0.12279, 8.1, 9.1, 10.0, 10.9, 12.0, 13.2, 14.6],
            [24, 0.1714, 12.1161, 0.12746, 8.9, 10.0, 11.0, 12.1, 13.3, 14.7, 16.4],
            [36, 0.1714, 14.0958, 0.13636, 10.1, 11.5, 12.7, 14.1, 15.7, 17.5, 19.7],
            [48, 0.1714, 16.1182, 0.14420, 11.2, 12.9, 14.4, 16.1, 18.0, 20.3, 23.2],
            [60, 0.1714, 18.2480, 0.15080, 12.5, 14.5, 16.2, 18.2, 20.5, 23.2, 26.6],
        ];

        foreach ($bbUPerempuan as $row) {
            $data[] = ['jenis_kelamin' => 'P', 'indikator' => 'BB_U',
                       'parameter' => $row[0], 'l_value' => $row[1],
                       'm_value' => $row[2], 's_value' => $row[3],
                       'sd_minus3' => $row[4], 'sd_minus2' => $row[5],
                       'sd_minus1' => $row[6], 'median_val' => $row[7],
                       'sd_plus1' => $row[8], 'sd_plus2' => $row[9], 'sd_plus3' => $row[10]];
        }

        // ── TB/U LAKI-LAKI (sampel) ────────────────────────────────────────
        $tbULaki = [
            [0,  1.0000, 49.8842, 0.03795, 44.2, 46.1, 48.0, 49.9, 51.8, 53.7, 55.6],
            [6,  1.0000, 67.6236, 0.03860, 61.2, 63.3, 65.5, 67.6, 69.8, 71.9, 74.0],
            [12, 1.0000, 75.7488, 0.04036, 68.6, 71.0, 73.4, 75.7, 78.0, 80.4, 82.7],
            [24, 1.0000, 87.7657, 0.04200, 79.3, 82.1, 85.0, 87.8, 90.6, 93.4, 96.2],
            [36, 1.0000, 96.0952, 0.04227, 86.8, 89.9, 93.0, 96.1, 99.2, 102.3, 105.4],
            [48, 1.0000, 103.3117, 0.04267, 93.3, 96.7, 100.0, 103.3, 106.6, 109.9, 113.3],
            [60, 1.0000, 110.0010, 0.04286, 99.4, 103.0, 106.5, 110.0, 113.5, 117.0, 120.5],
        ];

        foreach ($tbULaki as $row) {
            $data[] = ['jenis_kelamin' => 'L', 'indikator' => 'TB_U',
                       'parameter' => $row[0], 'l_value' => $row[1],
                       'm_value' => $row[2], 's_value' => $row[3],
                       'sd_minus3' => $row[4], 'sd_minus2' => $row[5],
                       'sd_minus1' => $row[6], 'median_val' => $row[7],
                       'sd_plus1' => $row[8], 'sd_plus2' => $row[9], 'sd_plus3' => $row[10]];
        }

        // ── TB/U PEREMPUAN (sampel) ────────────────────────────────────────
        $tbUPerempuan = [
            [0,  1.0000, 49.1477, 0.03790, 43.6, 45.4, 47.3, 49.1, 51.0, 52.9, 54.7],
            [6,  1.0000, 65.7301, 0.03870, 59.4, 61.5, 63.6, 65.7, 67.8, 70.0, 72.1],
            [12, 1.0000, 74.0151, 0.04000, 66.7, 69.2, 71.6, 74.0, 76.4, 78.9, 81.3],
            [24, 1.0000, 86.4153, 0.04145, 78.1, 80.9, 83.7, 86.4, 89.2, 92.0, 94.8],
            [36, 1.0000, 95.1258, 0.04155, 85.6, 88.7, 91.9, 95.1, 98.3, 101.5, 104.7],
            [48, 1.0000, 102.7186, 0.04198, 92.4, 95.8, 99.2, 102.7, 106.2, 109.7, 113.1],
            [60, 1.0000, 109.4413, 0.04256, 98.5, 102.1, 105.8, 109.4, 113.1, 116.7, 120.4],
        ];

        foreach ($tbUPerempuan as $row) {
            $data[] = ['jenis_kelamin' => 'P', 'indikator' => 'TB_U',
                       'parameter' => $row[0], 'l_value' => $row[1],
                       'm_value' => $row[2], 's_value' => $row[3],
                       'sd_minus3' => $row[4], 'sd_minus2' => $row[5],
                       'sd_minus1' => $row[6], 'median_val' => $row[7],
                       'sd_plus1' => $row[8], 'sd_plus2' => $row[9], 'sd_plus3' => $row[10]];
        }

        DB::table('who_zscore_reference')->insert($data);

        $this->command->warn('⚠️  WHO Z-score: Data SAMPEL berhasil di-seed (' . count($data) . ' baris).');
        $this->command->warn('   Untuk produksi, unduh data LENGKAP dari who.int dan jalankan WhoZscoreFullSeeder.');
    }
}
