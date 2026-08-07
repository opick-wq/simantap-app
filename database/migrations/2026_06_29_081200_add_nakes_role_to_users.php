<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('PRAGMA foreign_keys=OFF');

        DB::statement('
            CREATE TABLE users_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nama VARCHAR(100) NOT NULL,
                email VARCHAR(255) UNIQUE,
                nomor_hp VARCHAR(20),
                email_verified_at TIMESTAMP,
                password VARCHAR(255) NOT NULL,
                role VARCHAR(20) NOT NULL DEFAULT "kader"
                    CHECK(role IN ("kader","nakes","petugas","admin","dinas","orang_tua")),
                posyandu_id INTEGER,
                aktif BOOLEAN NOT NULL DEFAULT 1,
                remember_token VARCHAR(100),
                created_at TIMESTAMP,
                updated_at TIMESTAMP,
                FOREIGN KEY (posyandu_id) REFERENCES posyandu(id) ON DELETE SET NULL
            )
        ');

        DB::statement('
            INSERT INTO users_new
            SELECT id,nama,email,nomor_hp,email_verified_at,password,role,
                   posyandu_id,aktif,remember_token,created_at,updated_at
            FROM users
        ');

        DB::statement('DROP TABLE users');
        DB::statement('ALTER TABLE users_new RENAME TO users');
        DB::statement('CREATE INDEX users_role_index ON users(role)');
        DB::statement('PRAGMA foreign_keys=ON');
    }

    public function down(): void {}
};
