<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pohon', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pohon')->unique();
            $table->foreignId('proyek_id')->constrained('proyek')->cascadeOnDelete();
            $table->foreignId('jenis_pohon_id')->constrained('jenis_pohon')->restrictOnDelete();
            $table->foreignId('lokasi_hutan_id')->constrained('lokasi_hutan')->restrictOnDelete();
            $table->foreignId('kelompok_tani_id')->nullable()->constrained('kelompok_tani')->nullOnDelete();
            $table->unsignedInteger('tinggi_cm')->nullable();
            $table->decimal('diameter_cm', 8, 2)->nullable();
            $table->date('tanggal_tanam')->nullable();
            $table->string('status')->default('tersedia');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('catatan')->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pohon');
    }
};
