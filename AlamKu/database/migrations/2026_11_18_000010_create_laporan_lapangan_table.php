<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_lapangan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pohon_id')->nullable()->constrained('pohon')->nullOnDelete();
            $table->foreignId('proyek_id')->constrained('proyek')->cascadeOnDelete();
            $table->foreignId('pengguna_id')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->string('kondisi')->default('sehat');
            $table->unsignedInteger('tinggi_cm')->nullable();
            $table->decimal('diameter_cm', 8, 2)->nullable();
            $table->date('tanggal_laporan');
            $table->string('status')->default('draft');
            $table->text('catatan_verifikasi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_lapangan');
    }
};
