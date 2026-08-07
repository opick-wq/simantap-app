<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('balita', function (Blueprint $table) {
            $table->id();
            $table->foreignId('posyandu_id')->constrained('posyandu')->restrictOnDelete();
            $table->string('nik_balita', 20)->nullable();
            $table->string('nama', 100);
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['L', 'P']);

            // Data orang tua
            $table->string('nik_ibu', 20)->nullable();
            $table->string('nama_ibu', 100);
            $table->string('nomor_hp_ibu', 20)->nullable();
            $table->string('nik_ayah', 20)->nullable();
            $table->string('nama_ayah', 100)->nullable();
            $table->text('alamat');
            $table->string('rt_rw', 10)->nullable();

            // Data kelahiran
            $table->unsignedSmallInteger('berat_lahir_gram')->nullable()->comment('Berat lahir dalam gram');
            $table->decimal('panjang_lahir_cm', 4, 1)->nullable();
            $table->boolean('prematur')->default(false);
            $table->unsignedTinyInteger('usia_gestasi_minggu')->nullable()->comment('Diisi jika prematur');

            // Link akun orang tua (opsional, dibuat oleh kader)
            $table->foreignId('user_id_ortu')->nullable()->constrained('users')->nullOnDelete();

            // Status aktif
            $table->boolean('aktif')->default(true);
            $table->date('tanggal_nonaktif')->nullable();
            $table->string('alasan_nonaktif', 100)->nullable();

            $table->timestamps();

            $table->index('posyandu_id');
            $table->index('nik_balita');
            $table->index('nama');
            $table->index('aktif');
            $table->index('jenis_kelamin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('balita');
    }
};
