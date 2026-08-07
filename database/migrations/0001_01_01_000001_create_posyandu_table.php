<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('posyandu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wilayah_id')->constrained('wilayah')->restrictOnDelete();
            $table->string('nama', 100);
            $table->text('alamat');
            $table->string('jadwal_hari', 50)->nullable()->comment('Contoh: Rabu minggu ke-2');
            $table->time('jadwal_jam')->nullable();
            $table->decimal('koordinat_lat', 10, 8)->nullable();
            $table->decimal('koordinat_lng', 11, 8)->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index('wilayah_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posyandu');
    }
};
