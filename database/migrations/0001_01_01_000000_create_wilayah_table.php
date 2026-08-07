<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wilayah', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('kecamatan', 100);
            $table->string('kabupaten', 100);
            $table->string('provinsi', 100)->default('Jawa Barat');
            $table->string('kode_bps', 20)->nullable()->comment('Kode wilayah BPS untuk integrasi data dinas');
            $table->timestamps();

            $table->index('kabupaten');
            $table->index('kode_bps');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wilayah');
    }
};
