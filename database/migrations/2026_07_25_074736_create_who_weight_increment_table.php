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
        Schema::create('who_weight_increment', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_kelamin', 1); // L / P
            $table->unsignedTinyInteger('interval_bulan'); // 3, 4, atau 6
            $table->unsignedTinyInteger('usia_awal');      // 0..21
            $table->unsignedSmallInteger('min_gram');      // median P50 dari WHO
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('who_weight_increment');
    }
};
