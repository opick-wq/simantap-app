<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Data referensi WHO Z-score (wajib ada agar perhitungan gizi berjalan)
        $this->call(\Database\Seeders\WhoZscoreSeeder::class);

        // 2. Akun admin awal — password wajib diganti setelah login pertama
        \App\Models\User::create([
            'nama'     => 'Admin SI-MANTAP',
            'email'    => 'admin@simantap.id',
            'password' => Hash::make('123456'),
            'role'     => 'admin',
        ]);

        $this->command->info('✓ Seeder produksi selesai.');
        $this->command->info('  Login: admin@simantap.id / 123456');
        $this->command->warn('  ⚠ Segera ganti password setelah login pertama!');
    }
}
