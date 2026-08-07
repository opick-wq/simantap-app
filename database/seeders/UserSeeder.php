<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $posyandu = DB::table('posyandu')->orderBy('id')->get();
        $apel    = $posyandu[0]->id ?? 1;
        $mangga  = $posyandu[1]->id ?? 1;
        $anggur  = $posyandu[2]->id ?? 1;
        $jeruk   = $posyandu[3]->id ?? 1;

        $users = [
            // Admin
            [
                'posyandu_id' => $apel,
                'nama'        => 'Admin SI-MANTAP',
                'email'       => 'admin@simantap.dev',
                'password'    => Hash::make('password'),
                'role'        => 'admin',
                'nomor_hp'    => '081234560001',
                'aktif'       => true,
            ],
            // Kader per posyandu
            [
                'posyandu_id' => $apel,
                'nama'        => 'Siti Kader (Apel)',
                'email'       => 'kader@simantap.dev',
                'password'    => Hash::make('password'),
                'role'        => 'kader',
                'nomor_hp'    => '081234560002',
                'aktif'       => true,
            ],
            [
                'posyandu_id' => $mangga,
                'nama'        => 'Rina Kader (Mangga)',
                'email'       => 'kader.mangga@simantap.dev',
                'password'    => Hash::make('password'),
                'role'        => 'kader',
                'nomor_hp'    => '081234560006',
                'aktif'       => true,
            ],
            [
                'posyandu_id' => $anggur,
                'nama'        => 'Dewi Kader (Anggur)',
                'email'       => 'kader.anggur@simantap.dev',
                'password'    => Hash::make('password'),
                'role'        => 'kader',
                'nomor_hp'    => '081234560007',
                'aktif'       => true,
            ],
            [
                'posyandu_id' => $jeruk,
                'nama'        => 'Yuli Kader (Jeruk)',
                'email'       => 'kader.jeruk@simantap.dev',
                'password'    => Hash::make('password'),
                'role'        => 'kader',
                'nomor_hp'    => '081234560008',
                'aktif'       => true,
            ],
            // Petugas
            [
                'posyandu_id' => $apel,
                'nama'        => 'Bidan Dewi Petugas',
                'email'       => 'petugas@simantap.dev',
                'password'    => Hash::make('password'),
                'role'        => 'petugas',
                'nomor_hp'    => '081234560003',
                'aktif'       => true,
            ],
            // Nakes (1 per posyandu, demo di Apel)
            [
                'posyandu_id' => $apel,
                'nama'        => 'Bidan Sari Nakes',
                'email'       => 'nakes@simantap.dev',
                'password'    => Hash::make('password'),
                'role'        => 'nakes',
                'nomor_hp'    => '081234560010',
                'aktif'       => true,
            ],
            // Dinas
            [
                'posyandu_id' => null,
                'nama'        => 'Petugas Dinas Kesehatan',
                'email'       => 'dinas@simantap.dev',
                'password'    => Hash::make('password'),
                'role'        => 'dinas',
                'nomor_hp'    => '081234560004',
                'aktif'       => true,
            ],
            // Orang tua
            [
                'posyandu_id' => $apel,
                'nama'        => 'Ibu Rina (Demo Ortu)',
                'email'       => 'ortu@simantap.dev',
                'password'    => Hash::make('password'),
                'role'        => 'orang_tua',
                'nomor_hp'    => '081234560005',
                'aktif'       => true,
            ],
        ];

        foreach ($users as &$u) {
            $u['created_at'] = now();
            $u['updated_at'] = now();
        }

        DB::table('users')->insert($users);

        $this->command->info('✅ Akun demo berhasil dibuat:');
        $this->command->table(
            ['Email', 'Password', 'Role', 'Posyandu'],
            [
                ['admin@simantap.dev',         'password', 'admin',     'Apel'],
                ['kader@simantap.dev',          'password', 'kader',     'Apel'],
                ['kader.mangga@simantap.dev',   'password', 'kader',     'Mangga'],
                ['kader.anggur@simantap.dev',   'password', 'kader',     'Anggur'],
                ['kader.jeruk@simantap.dev',    'password', 'kader',     'Jeruk'],
                ['nakes@simantap.dev',          'password', 'nakes',     'Apel'],
                ['petugas@simantap.dev',        'password', 'petugas',   'semua'],
                ['dinas@simantap.dev',          'password', 'dinas',     'semua'],
                ['ortu@simantap.dev',           'password', 'orang_tua', 'Apel'],
            ]
        );
    }
}
