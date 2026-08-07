<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pengukuran', function (Blueprint $table) {
            // Posisi saat pengukuran tinggi/panjang badan
            // 'terlentang' = PB (0-24 bln), 'berdiri' = TB (>24 bln)
            // Digunakan untuk koreksi 0.7 cm sesuai Permenkes No. 2 Tahun 2020
            $table->enum('posisi_ukur', ['terlentang', 'berdiri'])
                  ->nullable()
                  ->after('tinggi_badan_cm');
        });
    }

    public function down(): void
    {
        Schema::table('pengukuran', function (Blueprint $table) {
            $table->dropColumn('posisi_ukur');
        });
    }
};
