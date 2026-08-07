<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WhoWeightIncrementSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('who_weight_increment')->truncate();

        // Tabel 17 — Interval 3 Bulan (Permenkes 2/2020)
        // [usia_awal, L_gram, P_gram]
        $tabel17 = [
            [0, 2083, 1784],
            [1, 1733, 1542],
            [2, 1284, 1197],
            [3, 940,  913],
            [4, 707,  694],
            [5, 550,  528],
            [6, 436,  400],
            [7, 346,  301],
            [8, 271,  230],
            [9, 210,  181],
            [10, 159, 147],
            [11, 119, 122],
            [12, 88,  102],
            [13, 65,  88],
            [14, 49,  78],
            [15, 38,  70],
            [16, 32,  62],
            [17, 28,  53],
            [18, 26,  43],
            [19, 24,  32],
            [20, 19,  20],
            [21, 10,  8],
        ];

        // Tabel 18 — Interval 4 Bulan (Permenkes 2/2020)
        $tabel18 = [
            [0, 2603, 2291],
            [1, 2138, 1924],
            [2, 1554, 1484],
            [3, 1181, 1152],
            [4, 933,  890],
            [5, 744,  689],
            [6, 602,  541],
            [7, 486,  435],
            [8, 401,  360],
            [9, 334,  303],
            [10, 280, 264],
            [11, 231, 235],
            [12, 199, 216],
            [13, 183, 206],
            [14, 175, 199],
            [15, 171, 192],
            [16, 169, 178],
            [17, 165, 168],
            [18, 161, 162],
            [19, 157, 162],
            [20, 157, 152],
        ];

        // Tabel 19 — Interval 6 Bulan (Permenkes 2/2020)
        $tabel19 = [
            [0, 3387, 3049],
            [1, 2759, 2498],
            [2, 2096, 1985],
            [3, 1636, 1563],
            [4, 1321, 1240],
            [5, 1080, 999],
            [6, 909,  824],
            [7, 778,  702],
            [8, 676,  619],
            [9, 599,  565],
            [10, 547, 532],
            [11, 515, 513],
            [12, 493, 501],
            [13, 479, 492],
            [14, 470, 484],
            [15, 465, 479],
            [16, 461, 475],
            [17, 456, 466],
            [18, 451, 425],
        ];

        $rows = [];
        foreach ($tabel17 as [$usia, $l, $p]) {
            $rows[] = ['jenis_kelamin' => 'L', 'interval_bulan' => 3, 'usia_awal' => $usia, 'min_gram' => $l];
            $rows[] = ['jenis_kelamin' => 'P', 'interval_bulan' => 3, 'usia_awal' => $usia, 'min_gram' => $p];
        }
        foreach ($tabel18 as [$usia, $l, $p]) {
            $rows[] = ['jenis_kelamin' => 'L', 'interval_bulan' => 4, 'usia_awal' => $usia, 'min_gram' => $l];
            $rows[] = ['jenis_kelamin' => 'P', 'interval_bulan' => 4, 'usia_awal' => $usia, 'min_gram' => $p];
        }
        foreach ($tabel19 as [$usia, $l, $p]) {
            $rows[] = ['jenis_kelamin' => 'L', 'interval_bulan' => 6, 'usia_awal' => $usia, 'min_gram' => $l];
            $rows[] = ['jenis_kelamin' => 'P', 'interval_bulan' => 6, 'usia_awal' => $usia, 'min_gram' => $p];
        }

        DB::table('who_weight_increment')->insert($rows);
    }
}
