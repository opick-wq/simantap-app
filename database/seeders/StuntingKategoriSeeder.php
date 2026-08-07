<?php

namespace Database\Seeders;

use App\Models\Balita;
use App\Models\Pengukuran;
use App\Models\Posyandu;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * 4 kategori stunting (TB/U) sesuai WHO 2006 & Permenkes No.2/2020:
 *
 *  TINGGI         z > +3 SD
 *  NORMAL        -2 SD s/d +3 SD
 *  PENDEK        -3 SD s/d < -2 SD   (Stunted)
 *  SANGAT_PENDEK  z < -3 SD          (Severely Stunted)
 */
class StuntingKategoriSeeder extends Seeder
{
    public function run(): void
    {
        $posyanduId = Posyandu::value('id');
        $kaderId    = User::where('role', 'kader')->value('id');

        // Anak 24 bulan laki-laki: WHO median TB/U ≈ 87.1 cm, S ≈ 0.038
        // z = ln(TB/M) / S  →  TB = M * exp(S*z)
        $kasus = [
            [
                'nik'   => '3201030303030001',
                'nama'  => 'Kenzo Tinggi',
                'jk'    => 'L',
                'bulan' => 24,
                // z > +3  →  TB > 87.1 * exp(0.038*3) = 87.1 * 1.120 ≈ 97.6 cm
                'bb'    => 14.0, 'tb' => 99.0,
                'label' => 'TINGGI (z TB/U > +3)',
            ],
            [
                'nik'   => '3201030303030002',
                'nama'  => 'Laras Normal',
                'jk'    => 'P',
                'bulan' => 24,
                // z ≈ 0  →  TB median perempuan 24 bln ≈ 85.7 cm
                'bb'    => 11.5, 'tb' => 86.0,
                'label' => 'NORMAL (-2 ≤ z ≤ +3)',
            ],
            [
                'nik'   => '3201030303030003',
                'nama'  => 'Miko Pendek',
                'jk'    => 'L',
                'bulan' => 24,
                // z ≈ -2.5  →  TB = 87.1 * exp(0.038*-2.5) = 87.1 * 0.909 ≈ 79.2 cm
                'bb'    => 10.5, 'tb' => 79.0,
                'label' => 'PENDEK / Stunted (-3 ≤ z < -2)',
            ],
            [
                'nik'   => '3201030303030004',
                'nama'  => 'Nadia Sangat Pendek',
                'jk'    => 'P',
                'bulan' => 24,
                // z < -3  →  TB perempuan = 85.7 * exp(0.038*-3.5) = 85.7 * 0.875 ≈ 75.0 cm
                'bb'    => 9.0,  'tb' => 74.0,
                'label' => 'SANGAT PENDEK / Severely Stunted (z < -3)',
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
                'alamat'        => 'Jl. Stunting No. ' . substr($k['nik'], -1),
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

            $this->command->info("✓ [{$k['label']}] {$k['nama']} TB={$k['tb']}cm");
        }
    }
}
