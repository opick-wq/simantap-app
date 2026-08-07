<?php

namespace Database\Seeders;

use App\Models\Balita;
use App\Models\Pengukuran;
use App\Models\Posyandu;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Sampel balita mencerminkan 5 kategori status gizi (berdasarkan z-score BB/U):
 *  GIZI_BURUK      z < -3
 *  GIZI_KURANG    -3 ≤ z < -2
 *  GIZI_BAIK      -2 ≤ z ≤ +2
 *  BERISIKO_LEBIH +2 < z ≤ +3
 *  GIZI_LEBIH      z > +3
 */
class GiziKategoriSeeder extends Seeder
{
    public function run(): void
    {
        $posyanduId = Posyandu::value('id');
        $kaderId    = User::where('role', 'kader')->value('id');

        // Anak laki-laki 24 bulan: median BB/U = 12.2 kg, SD ≈ 1.2
        // z = (BB - M) / SD  →  BB = M + z*SD
        $kasus = [
            [
                'nik'   => '3201020202020001',
                'nama'  => 'Farhan Gizi Buruk',
                'jk'    => 'L',
                'bulan' => 24,
                // z ≈ -3.5 → BB = 12.2 + (-3.5 × 1.2) = 7.99 ≈ 8.0 kg
                'bb'    => 8.0,  'tb' => 83.0,
                'label' => 'GIZI_BURUK (z BB/U < -3)',
            ],
            [
                'nik'   => '3201020202020002',
                'nama'  => 'Gilang Gizi Kurang',
                'jk'    => 'L',
                'bulan' => 24,
                // z ≈ -2.5 → BB dikalibrasi dari hasil nyata
                'bb'    => 9.8,  'tb' => 85.0,
                'label' => 'GIZI_KURANG (-3 ≤ z < -2)',
            ],
            [
                'nik'   => '3201020202020003',
                'nama'  => 'Hana Gizi Baik',
                'jk'    => 'P',
                'bulan' => 24,
                // z ≈ 0 → BB median perempuan 24 bln ≈ 11.5 kg
                'bb'    => 11.5, 'tb' => 87.0,
                'label' => 'GIZI_BAIK (-2 ≤ z ≤ +2)',
            ],
            [
                'nik'   => '3201020202020004',
                'nama'  => 'Ivan Berisiko Lebih',
                'jk'    => 'L',
                'bulan' => 24,
                // z ≈ +2.5 → BB = 12.2 + (2.5 × 1.2) = 15.2 kg
                'bb'    => 15.2, 'tb' => 88.0,
                'label' => 'BERISIKO_LEBIH (+2 < z ≤ +3)',
            ],
            [
                'nik'   => '3201020202020005',
                'nama'  => 'Julia Gizi Lebih',
                'jk'    => 'P',
                'bulan' => 24,
                // z ≈ +3.5 → BB perempuan 24 bln: M=11.5 + (3.5×1.1) = 15.4 kg
                'bb'    => 16.0, 'tb' => 88.5,
                'label' => 'GIZI_LEBIH (z > +3)',
            ],
        ];

        foreach ($kasus as $k) {
            $existing = Balita::where('nik_balita', $k['nik'])->first();
            if ($existing) {
                $existing->pengukuran()->delete();
                $existing->delete();
            }

            $tglLahir = Carbon::now()->subMonths($k['bulan'])->format('Y-m-d');

            $balita = Balita::create([
                'posyandu_id'   => $posyanduId,
                'nik_balita'    => $k['nik'],
                'nama'          => $k['nama'],
                'jenis_kelamin' => $k['jk'],
                'tanggal_lahir' => $tglLahir,
                'nama_ibu'      => 'Ibu ' . explode(' ', $k['nama'])[0],
                'alamat'        => 'Jl. Contoh No. ' . substr($k['nik'], -1),
                'aktif'         => true,
            ]);

            Pengukuran::create([
                'balita_id'       => $balita->id,
                'dicatat_oleh'    => $kaderId,
                'tanggal_ukur'    => today(),
                'berat_badan_kg'  => $k['bb'],
                'tinggi_badan_cm' => $k['tb'],
                'umur_bulan'      => $k['bulan'],
            ]);

            $this->command->info("✓ [{$k['label']}] {$k['nama']} BB={$k['bb']}kg");
        }
    }
}
