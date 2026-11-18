<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proyek', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lokasi_hutan_id')->constrained('lokasi_hutan')->cascadeOnDelete();
            $table->foreignId('kelompok_tani_id')->constrained('kelompok_tani')->cascadeOnDelete();
            $table->foreignId('jenis_pohon_id')->constrained('jenis_pohon')->restrictOnDelete();
            $table->string('judul');
            $table->text('deskripsi');
            $table->text('target_ekologi');
            $table->unsignedBigInteger('biaya_per_bibit');
            $table->unsignedInteger('kuota_bibit');
            $table->unsignedInteger('bibit_dipesan')->default(0);
            $table->date('tanggal_mulai_tanam');
            $table->date('tanggal_selesai_tanam');
            $table->enum('status', ['draf', 'aktif', 'penuh', 'selesai', 'nonaktif'])->default('draf');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proyek');
    }
};
