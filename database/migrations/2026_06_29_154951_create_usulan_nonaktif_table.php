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
        Schema::create('usulan_nonaktif', function (Blueprint $table) {
            $table->id();
            $table->foreignId('balita_id')->constrained('balita')->cascadeOnDelete();
            $table->foreignId('pengusul_id')->constrained('users');
            $table->text('alasan');
            $table->enum('status', ['DIUSULKAN', 'DITERUSKAN', 'DITOLAK', 'DISETUJUI'])
                  ->default('DIUSULKAN');
            $table->foreignId('nakes_id')->nullable()->constrained('users');
            $table->text('catatan_nakes')->nullable();
            $table->foreignId('petugas_id')->nullable()->constrained('users');
            $table->text('catatan_petugas')->nullable();
            $table->enum('tindakan_akhir', ['NONAKTIF', 'HAPUS'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usulan_nonaktif');
    }
};
