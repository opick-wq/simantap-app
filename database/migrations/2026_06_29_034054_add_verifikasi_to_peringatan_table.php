<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peringatan', function (Blueprint $table) {
            $table->string('status_verifikasi', 20)->default('BELUM')->after('status_tindak_lanjut');
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diverifikasi_pada')->nullable();
            $table->text('catatan_verifikasi')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('peringatan', function (Blueprint $table) {
            $table->dropColumn(['status_verifikasi', 'diverifikasi_oleh', 'diverifikasi_pada', 'catatan_verifikasi']);
        });
    }
};
