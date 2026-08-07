<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Matikan foreign key check sementara
        DB::statement('PRAGMA foreign_keys = OFF');

        // 1. Amankan data lama ke tabel temporary
        DB::statement('CREATE TABLE who_zscore_reference_new AS SELECT * FROM who_zscore_reference');
        
        // 2. Hapus tabel lama
        DB::statement('DROP TABLE who_zscore_reference');
        
        // 3. Buat tabel baru dengan 16 kolom
        DB::statement("CREATE TABLE who_zscore_reference (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            jenis_kelamin VARCHAR(1) NOT NULL CHECK (jenis_kelamin IN ('L', 'P')),
            indikator VARCHAR(10) NOT NULL CHECK (indikator IN ('BB_U', 'TB_U', 'BB_TB', 'IMT_U')),
            parameter REAL NOT NULL,
            l_value REAL NOT NULL,
            m_value REAL NOT NULL,
            s_value REAL NOT NULL,
            sd_minus3 REAL,
            sd_minus2 REAL,
            sd_minus1 REAL,
            median_val REAL,
            sd_plus1 REAL,
            sd_plus2 REAL,
            sd_plus3 REAL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL
        )");

        // 4. INI YANG DIPERBAIKI: Masukkan 14 data lama secara spesifik ke 14 kolom yang sesuai
        DB::statement('
            INSERT INTO who_zscore_reference (
                id, jenis_kelamin, indikator, parameter, l_value, m_value, s_value, 
                sd_minus3, sd_minus2, sd_minus1, median_val, sd_plus1, sd_plus2, sd_plus3
            ) 
            SELECT 
                id, jenis_kelamin, indikator, parameter, l_value, m_value, s_value, 
                sd_minus3, sd_minus2, sd_minus1, median_val, sd_plus1, sd_plus2, sd_plus3 
            FROM who_zscore_reference_new
        ');

        // 5. Bersihkan tabel temporary
        DB::statement('DROP TABLE who_zscore_reference_new');
        
        // 6. Buat ulang index
        DB::statement('CREATE UNIQUE INDEX uq_who_ref ON who_zscore_reference (jenis_kelamin, indikator, parameter)');
        DB::statement('CREATE INDEX idx_lookup ON who_zscore_reference (indikator, jenis_kelamin, parameter)');

        // Nyalakan kembali foreign key check
        DB::statement('PRAGMA foreign_keys = ON');
    }

    public function down(): void
    {
        // Tidak rollback — tidak merusak data yang ada
    }
};