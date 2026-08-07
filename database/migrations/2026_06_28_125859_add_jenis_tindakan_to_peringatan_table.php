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
        Schema::table('peringatan', function (Blueprint $table) {
            $table->json('jenis_tindakan')->nullable()->after('catatan_petugas');
            $table->boolean('dilaporkan_ke_atasan')->default(false)->after('jenis_tindakan');
        });
    }

    public function down(): void
    {
        Schema::table('peringatan', function (Blueprint $table) {
            $table->dropColumn(['jenis_tindakan', 'dilaporkan_ke_atasan']);
        });
    }
};
