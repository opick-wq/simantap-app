<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengukuran', function (Blueprint $table) {
            // Selisih BB dari pengukuran sebelumnya (gram, bisa negatif)
            $table->float('kenaikan_bb_gram')->nullable()->after('berat_badan_kg');
            // Status kenaikan berat badan berdasarkan standar KMS
            // N = Naik cukup, T = Tidak naik cukup, O = Turun/Tidak naik
            $table->string('status_kbb', 1)->nullable()->after('kenaikan_bb_gram');
        });
    }

    public function down(): void
    {
        Schema::table('pengukuran', function (Blueprint $table) {
            $table->dropColumn(['kenaikan_bb_gram', 'status_kbb']);
        });
    }
};
