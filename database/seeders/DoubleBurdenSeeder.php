<?php

namespace Database\Seeders;

use App\Models\Balita;
use App\Models\Pengukuran;
use App\Models\Posyandu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Membuat 4 sampel balita untuk demonstrasi irisan Gizi Buruk × Stunting:
 *
 *                    STUNTING
 *                  Ya          Tidak
 *           ┌──────────┬──────────────┐
 *     Ya    │ Stunted  │   Wasted     │
 * GIZI      │ + Wasted │   only       │
 * BURUK     ├──────────┼──────────────┤
 *     Tidak │ Stunted  │   Normal     │
 *           │   only   │              │
 *           └──────────┴──────────────┘
 */
class DoubleBurdenSeeder extends Seeder
{
    public function run(): void
    {
        $posyanduId = Posyandu::value('id');
        $kaderId    = \App\Models\User::where('role', 'kader')->value('id');

        $kasus = [
            // ── Kuadran 1: Stunted + Wasted (Double Burden) ──────────────────
            [
                'balita' => [
                    'nik_balita'    => '3201010101010001',
                    'nama'          => 'Ahmad Rizki Pratama',
                    'jenis_kelamin' => 'L',
                    'tanggal_lahir' => Carbon::now()->subMonths(24)->format('Y-m-d'),
                    'nama_ibu'      => 'Sari Pratama',
                    'alamat'        => 'Jl. Melati No. 1',
                ],
                // Anak 24 bln L: BB sangat rendah + TB sangat pendek
                // BB/TB z-score < -3 (gizi buruk), TB/U z-score < -2 (stunting)
                'ukur' => ['bb' => 7.5, 'tb' => 78.0],
                'label' => 'Stunted + Wasted (Double Burden)',
            ],

            // ── Kuadran 2: Wasted only (Gizi Buruk, TB normal) ───────────────
            [
                'balita' => [
                    'nik_balita'    => '3201010101010002',
                    'nama'          => 'Bunga Cantika Dewi',
                    'jenis_kelamin' => 'P',
                    'tanggal_lahir' => Carbon::now()->subMonths(18)->format('Y-m-d'),
                    'nama_ibu'      => 'Dewi Lestari',
                    'alamat'        => 'Jl. Anggrek No. 2',
                ],
                // Anak 18 bln P: BB sangat rendah, TB masih cukup (TB/U normal)
                // BB/TB z-score < -3, TB/U z-score > -2
                'ukur' => ['bb' => 6.8, 'tb' => 78.0],
                'label' => 'Wasted Only (Gizi Buruk tanpa Stunting)',
            ],

            // ── Kuadran 3: Stunted only (TB pendek, BB proporsional) ─────────
            [
                'balita' => [
                    'nik_balita'    => '3201010101010003',
                    'nama'          => 'Cahyo Purnomo',
                    'jenis_kelamin' => 'L',
                    'tanggal_lahir' => Carbon::now()->subMonths(36)->format('Y-m-d'),
                    'nama_ibu'      => 'Yuni Purnomo',
                    'alamat'        => 'Jl. Mawar No. 3',
                ],
                // Anak 36 bln L: TB sangat pendek, BB proporsional terhadap TB
                // TB/U z-score < -2 (stunting), BB/TB z-score > -2 (bukan gizi buruk)
                'ukur' => ['bb' => 10.5, 'tb' => 82.0],
                'label' => 'Stunted Only (tanpa Gizi Buruk)',
            ],

            // ── Kuadran 4: Normal ─────────────────────────────────────────────
            [
                'balita' => [
                    'nik_balita'    => '3201010101010004',
                    'nama'          => 'Dinda Ayu Lestari',
                    'jenis_kelamin' => 'P',
                    'tanggal_lahir' => Carbon::now()->subMonths(30)->format('Y-m-d'),
                    'nama_ibu'      => 'Rina Lestari',
                    'alamat'        => 'Jl. Kenanga No. 4',
                ],
                // Anak 30 bln P: BB dan TB normal
                'ukur' => ['bb' => 12.5, 'tb' => 89.0],
                'label' => 'Normal',
            ],
        ];

        foreach ($kasus as $k) {
            // Hapus jika sudah ada (re-run seeder)
            $existing = Balita::where('nik_balita', $k['balita']['nik_balita'])->first();
            if ($existing) {
                $existing->pengukuran()->delete();
                $existing->delete();
            }

            $balita = Balita::create(array_merge($k['balita'], [
                'posyandu_id' => $posyanduId,
                'aktif'       => true,
            ]));

            // Buat pengukuran hari ini agar masuk statistik bulan ini
            Pengukuran::create([
                'balita_id'       => $balita->id,
                'dicatat_oleh'    => $kaderId,
                'tanggal_ukur'    => today(),
                'berat_badan_kg'  => $k['ukur']['bb'],
                'tinggi_badan_cm' => $k['ukur']['tb'],
                'umur_bulan'      => (int) Carbon::parse($k['balita']['tanggal_lahir'])->diffInMonths(today()),
            ]);
            // Observer PengukuranObserver akan otomatis hitung z-score & EWS

            $this->command->info("✓ [{$k['label']}] {$k['balita']['nama']}");
        }
    }
}
