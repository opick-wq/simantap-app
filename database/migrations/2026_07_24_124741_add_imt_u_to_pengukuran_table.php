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
        Schema::table('pengukuran', function (Blueprint $table) {
            $table->float('imt_u')->nullable()->after('tinggi_badan_cm');
            $table->float('z_score_imt_u')->nullable()->after('z_score_bb_tb');
            $table->string('status_imt_u')->nullable()->after('status_wasting');
        });
    }

    public function down(): void
    {
        Schema::table('pengukuran', function (Blueprint $table) {
            $table->dropColumn(['imt_u', 'z_score_imt_u', 'status_imt_u']);
        });
    }
};
