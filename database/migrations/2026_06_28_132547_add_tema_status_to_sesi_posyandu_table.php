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
        Schema::table('sesi_posyandu', function (Blueprint $table) {
            $table->string('tema', 200)->nullable()->after('tanggal');
            $table->string('status', 20)->default('TERJADWAL')->after('tema');
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->after('dipimpin_oleh');
        });
    }

    public function down(): void
    {
        Schema::table('sesi_posyandu', function (Blueprint $table) {
            $table->dropColumn(['tema', 'status', 'dibuat_oleh']);
        });
    }
};
