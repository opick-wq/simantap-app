<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WilayahPosyanduSeeder extends Seeder
{
    public function run(): void
    {
        $wilayahId = DB::table('wilayah')->insertGetId([
            'nama'       => 'Kelurahan Sukamaju',
            'kecamatan'  => 'Kecamatan Cibeunying Kaler',
            'kabupaten'  => 'Kota Bandung',
            'provinsi'   => 'Jawa Barat',
            'kode_bps'   => '3273040001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('posyandu')->insert([
            [
                'wilayah_id'  => $wilayahId,
                'nama'        => 'Posyandu Apel',
                'alamat'      => 'Jl. Melati No. 12, RW 03, Kelurahan Sukamaju',
                'jadwal_hari' => 'Rabu minggu ke-2',
                'jadwal_jam'  => '08:00:00',
                'aktif'       => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'wilayah_id'  => $wilayahId,
                'nama'        => 'Posyandu Mangga',
                'alamat'      => 'Jl. Kenanga No. 5, RW 07, Kelurahan Sukamaju',
                'jadwal_hari' => 'Kamis minggu ke-2',
                'jadwal_jam'  => '08:30:00',
                'aktif'       => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'wilayah_id'  => $wilayahId,
                'nama'        => 'Posyandu Anggur',
                'alamat'      => 'Jl. Mawar No. 3, RW 11, Kelurahan Sukamaju',
                'jadwal_hari' => 'Jumat minggu ke-2',
                'jadwal_jam'  => '09:00:00',
                'aktif'       => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'wilayah_id'  => $wilayahId,
                'nama'        => 'Posyandu Jeruk',
                'alamat'      => 'Jl. Dahlia No. 8, RW 15, Kelurahan Sukamaju',
                'jadwal_hari' => 'Sabtu minggu ke-2',
                'jadwal_jam'  => '08:00:00',
                'aktif'       => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }
}
