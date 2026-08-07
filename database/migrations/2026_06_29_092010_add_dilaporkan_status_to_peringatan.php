<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite: recreate table dengan enum baru + kolom kader
        DB::statement('PRAGMA foreign_keys=OFF');

        DB::statement('
            CREATE TABLE peringatan_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                pengukuran_id INTEGER,
                balita_id INTEGER NOT NULL,
                jenis_peringatan VARCHAR(50) NOT NULL,
                level_risiko VARCHAR(10) NOT NULL CHECK(level_risiko IN ("MERAH","KUNING")),
                pesan TEXT NOT NULL,
                status_tindak_lanjut VARCHAR(20) NOT NULL DEFAULT "BELUM"
                    CHECK(status_tindak_lanjut IN ("BELUM","DILAPORKAN","DALAM_PROSES","SELESAI")),
                status_verifikasi VARCHAR(20) NOT NULL DEFAULT "BELUM"
                    CHECK(status_verifikasi IN ("BELUM","DISETUJUI","DITOLAK")),
                -- Kolom kader (laporan operasional)
                sudah_verifikasi_ulang BOOLEAN NOT NULL DEFAULT 0,
                sudah_eskalasi_nakes   BOOLEAN NOT NULL DEFAULT 0,
                catatan_kader          TEXT,
                dilaporkan_oleh        INTEGER,
                dilaporkan_pada        TIMESTAMP,
                -- Kolom nakes/petugas (intervensi klinis)
                jenis_tindakan         TEXT,
                dilaporkan_ke_atasan   BOOLEAN NOT NULL DEFAULT 0,
                catatan_petugas        TEXT,
                ditindaklanjuti_oleh   INTEGER,
                ditindaklanjuti_pada   TIMESTAMP,
                -- Kolom verifikasi petugas
                diverifikasi_oleh      INTEGER,
                diverifikasi_pada      TIMESTAMP,
                catatan_verifikasi     TEXT,
                created_at TIMESTAMP,
                updated_at TIMESTAMP,
                FOREIGN KEY (pengukuran_id) REFERENCES pengukuran(id) ON DELETE SET NULL,
                FOREIGN KEY (balita_id)     REFERENCES balita(id) ON DELETE CASCADE,
                FOREIGN KEY (dilaporkan_oleh)      REFERENCES users(id) ON DELETE SET NULL,
                FOREIGN KEY (ditindaklanjuti_oleh) REFERENCES users(id) ON DELETE SET NULL,
                FOREIGN KEY (diverifikasi_oleh)    REFERENCES users(id) ON DELETE SET NULL
            )
        ');

        // Copy data lama
        DB::statement('
            INSERT INTO peringatan_new
            (id, pengukuran_id, balita_id, jenis_peringatan, level_risiko, pesan,
             status_tindak_lanjut, status_verifikasi,
             sudah_verifikasi_ulang, sudah_eskalasi_nakes,
             jenis_tindakan, dilaporkan_ke_atasan, catatan_petugas,
             ditindaklanjuti_oleh, ditindaklanjuti_pada,
             diverifikasi_oleh, diverifikasi_pada, catatan_verifikasi,
             created_at, updated_at)
            SELECT
             id, pengukuran_id, balita_id, jenis_peringatan, level_risiko, pesan,
             status_tindak_lanjut, status_verifikasi,
             0, 0,
             jenis_tindakan, dilaporkan_ke_atasan, catatan_petugas,
             ditindaklanjuti_oleh, ditindaklanjuti_pada,
             diverifikasi_oleh, diverifikasi_pada, catatan_verifikasi,
             created_at, updated_at
            FROM peringatan
        ');

        DB::statement('DROP TABLE peringatan');
        DB::statement('ALTER TABLE peringatan_new RENAME TO peringatan');
        DB::statement('CREATE INDEX peringatan_balita_id_index ON peringatan(balita_id)');
        DB::statement('CREATE INDEX peringatan_status_index ON peringatan(status_tindak_lanjut)');
        DB::statement('PRAGMA foreign_keys=ON');
    }

    public function down(): void {}
};
