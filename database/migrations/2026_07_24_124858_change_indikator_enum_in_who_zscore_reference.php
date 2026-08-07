<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite tidak bisa ALTER CHECK constraint, jadi rekreasi tabel
        DB::statement('PRAGMA foreign_keys = OFF');

        DB::statement('CREATE TABLE who_zscore_reference_new AS SELECT * FROM who_zscore_reference');
        DB::statement('DROP TABLE who_zscore_reference');
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
            created_at DATETIME,
            updated_at DATETIME
        )");
        DB::statement('INSERT INTO who_zscore_reference SELECT * FROM who_zscore_reference_new');
        DB::statement('DROP TABLE who_zscore_reference_new');
        DB::statement('CREATE UNIQUE INDEX uq_who_ref ON who_zscore_reference (jenis_kelamin, indikator, parameter)');
        DB::statement('CREATE INDEX idx_lookup ON who_zscore_reference (indikator, jenis_kelamin, parameter)');

        DB::statement('PRAGMA foreign_keys = ON');
    }

    public function down(): void
    {
        // Tidak rollback — tidak merusak data yang ada
    }
};
