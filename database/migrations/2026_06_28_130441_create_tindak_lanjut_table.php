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
        Schema::create('tindak_lanjut', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peringatan_id')->constrained('peringatan')->cascadeOnDelete();
            $table->unsignedBigInteger('balita_id')->index();
            $table->foreignId('dicatat_oleh')->constrained('users');
            $table->json('jenis_tindakan');
            $table->boolean('dilaporkan_ke_atasan')->default(false);
            $table->text('catatan')->nullable();
            $table->string('status_akhir')->default('DALAM_PROSES'); // DALAM_PROSES | SELESAI
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tindak_lanjut');
    }
};
