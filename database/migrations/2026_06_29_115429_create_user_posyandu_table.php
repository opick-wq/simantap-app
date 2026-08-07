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
        Schema::create('user_posyandu', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('posyandu_id')->constrained('posyandu')->cascadeOnDelete();
            $table->primary(['user_id', 'posyandu_id']);
        });

        // Migrasi data nakes yang sudah ada dari kolom posyandu_id
        $nakes = DB::table('users')->where('role', 'nakes')->whereNotNull('posyandu_id')->get();
        foreach ($nakes as $n) {
            DB::table('user_posyandu')->insertOrIgnore([
                'user_id'     => $n->id,
                'posyandu_id' => $n->posyandu_id,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_posyandu');
    }
};
