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
        Schema::table('posyandu', function (Blueprint $table) {
            $table->tinyInteger('jadwal_minggu')->nullable()->after('jadwal_hari'); // 1-4 = minggu ke-1 s/d ke-4
        });
    }

    public function down(): void
    {
        Schema::table('posyandu', function (Blueprint $table) {
            $table->dropColumn('jadwal_minggu');
        });
    }
};
