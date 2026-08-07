<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sesi_posyandu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('posyandu_id')->constrained('posyandu')->restrictOnDelete();
            $table->foreignId('dipimpin_oleh')->constrained('users')->restrictOnDelete();
            $table->date('tanggal');
            $table->unsignedTinyInteger('jumlah_hadir')->nullable();
            $table->text('catatan')->nullable();
            $table->boolean('selesai')->default(false);
            $table->timestamps();

            $table->unique(['posyandu_id', 'tanggal']);
            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesi_posyandu');
    }
};
