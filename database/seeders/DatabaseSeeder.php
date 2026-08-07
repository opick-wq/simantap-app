<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // URUTAN PENTING — ada FK dependencies
        $this->call([
            WhoZscoreFullSeeder::class, // 1. Data referensi WHO lengkap 0-60 bulan (harus PERTAMA)
            WilayahPosyanduSeeder::class, // 2. Wilayah & Posyandu Apel
            UserSeeder::class,         // 3. Akun demo semua role
            DummyBalitaSeeder::class,  // 4. Balita & pengukuran dummy
        ]);
    }
}
