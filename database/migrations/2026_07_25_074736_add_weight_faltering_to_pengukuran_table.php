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
            $table->boolean('is_weight_faltering')->nullable()->after('status_kbb');
        });
    }

    public function down(): void
    {
        Schema::table('pengukuran', function (Blueprint $table) {
            $table->dropColumn('is_weight_faltering');
        });
    }
};
