<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengukuran', function (Blueprint $table) {
            $table->boolean('is_validated')->default(false)->after('flag_ews');
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->text('catatan_validasi')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pengukuran', function (Blueprint $table) {
            $table->dropColumn(['is_validated', 'validated_by', 'validated_at', 'catatan_validasi']);
        });
    }
};
