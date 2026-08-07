<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('who_zscore_reference', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->enum('jenis_kelamin', ['L', 'P'])->comment('L=Laki-laki, P=Perempuan');
            // Untuk BB/U dan TB/U: parameter = umur_bulan (0-60)
            // Untuk BB/TB: parameter = tinggi_badan_cm (45.0-120.0)
            $table->decimal('parameter', 5, 1)->comment('Umur (bulan) atau Tinggi (cm)');
            $table->enum('indikator', ['BB_U', 'TB_U', 'BB_TB']);

            // Nilai LMS untuk kalkulasi Z-score
            $table->decimal('l_value', 10, 6)->comment('Lambda - Box-Cox power');
            $table->decimal('m_value', 10, 6)->comment('Mu - median');
            $table->decimal('s_value', 10, 6)->comment('Sigma - CV');

            // Nilai SD untuk grafik
            $table->decimal('sd_minus3', 6, 2);
            $table->decimal('sd_minus2', 6, 2);
            $table->decimal('sd_minus1', 6, 2);
            $table->decimal('median_val', 6, 2);
            $table->decimal('sd_plus1', 6, 2);
            $table->decimal('sd_plus2', 6, 2);
            $table->decimal('sd_plus3', 6, 2);

            $table->unique(['jenis_kelamin', 'indikator', 'parameter'], 'uq_who_ref');
            $table->index(['indikator', 'jenis_kelamin', 'parameter'], 'idx_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('who_zscore_reference');
    }
};
