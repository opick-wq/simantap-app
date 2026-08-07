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
        // Isi NIK sementara untuk data lama yang belum punya NIK
        $blanks = DB::table('balita')->whereNull('nik_balita')->orWhere('nik_balita', '')->get(['id']);
        foreach ($blanks as $b) {
            DB::table('balita')->where('id', $b->id)
                ->update(['nik_balita' => 'TEMP' . str_pad($b->id, 12, '0', STR_PAD_LEFT)]);
        }

        Schema::table('balita', function (Blueprint $table) {
            $table->string('nik_balita', 16)->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('balita', function (Blueprint $table) {
            $table->dropUnique(['nik_balita']);
            $table->string('nik_balita', 20)->nullable()->change();
        });
    }
};
