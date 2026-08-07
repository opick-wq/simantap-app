<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('peringatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengukuran_id')->constrained('pengukuran')->cascadeOnDelete();
            $table->foreignId('balita_id')->constrained('balita')->cascadeOnDelete();

            $table->enum('jenis_peringatan', [
                'GIZI_BURUK', 'GIZI_KURANG', 'RISIKO_GIZI',
                'SANGAT_PENDEK', 'PENDEK_STUNTED', 'RISIKO_PENDEK',
                'WEIGHT_LOSS', 'WEIGHT_STAGNATION',
                'ZSCORE_DROP', 'ZSCORE_DROP_MILD', 'ZSCORE_DROP_PROG',
                'ABSEN_2BULAN', 'ABSEN_LAMA',
            ]);

            $table->enum('level_risiko', ['KUNING', 'MERAH']);
            $table->string('pesan', 255);

            $table->enum('status_tindak_lanjut', [
                'BELUM', 'DALAM_PROSES', 'SELESAI'
            ])->default('BELUM');

            $table->foreignId('ditindaklanjuti_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan_petugas')->nullable();
            $table->timestamp('ditindaklanjuti_pada')->nullable();

            $table->timestamps();

            $table->index(['balita_id', 'level_risiko']);
            $table->index('status_tindak_lanjut');
            $table->index(['level_risiko', 'created_at']);
            $table->index('jenis_peringatan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peringatan');
    }
};
