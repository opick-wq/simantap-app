<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pengukuran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('balita_id')->constrained('balita')->restrictOnDelete();
            $table->foreignId('sesi_id')->nullable()->constrained('sesi_posyandu')->nullOnDelete();
            $table->foreignId('dicatat_oleh')->constrained('users')->restrictOnDelete();

            // Waktu
            $table->date('tanggal_ukur');
            $table->unsignedTinyInteger('umur_bulan')->comment('Dihitung otomatis');
            $table->unsignedTinyInteger('umur_bulan_adjusted')->nullable()->comment('Jika prematur');

            // Antropometri WAJIB
            $table->decimal('berat_badan_kg', 4, 2)->comment('Range: 1.00-50.00 kg');
            $table->decimal('tinggi_badan_cm', 4, 1)->comment('Range: 40.0-130.0 cm');

            // Antropometri OPSIONAL
            $table->decimal('lingkar_lengan_atas_cm', 4, 1)->nullable();
            $table->decimal('lingkar_kepala_cm', 4, 1)->nullable();

            // Z-score (dihitung otomatis)
            $table->decimal('z_score_bb_u', 5, 3)->nullable();
            $table->decimal('z_score_tb_u', 5, 3)->nullable();
            $table->decimal('z_score_bb_tb', 5, 3)->nullable();

            // Status gizi (enum otomatis)
            $table->enum('status_gizi', [
                'GIZI_BURUK', 'GIZI_KURANG', 'GIZI_BAIK', 'BERISIKO_LEBIH', 'GIZI_LEBIH'
            ])->nullable();

            $table->enum('status_stunting', [
                'SANGAT_PENDEK', 'PENDEK', 'NORMAL', 'TINGGI'
            ])->nullable();

            $table->enum('status_wasting', [
                'SANGAT_KURUS', 'KURUS', 'NORMAL', 'GEMUK'
            ])->nullable();

            // Flag EWS keseluruhan
            $table->enum('flag_ews', ['HIJAU', 'KUNING', 'MERAH'])->default('HIJAU');

            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['balita_id', 'tanggal_ukur']);
            $table->index('flag_ews');
            $table->index('status_gizi');
            $table->index('tanggal_ukur');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengukuran');
    }
};
