<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelompok_tani', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengguna_id')->unique()->constrained('pengguna')->cascadeOnDelete();
            $table->string('nama_kelompok');
            $table->string('provinsi');
            $table->string('kabupaten');
            $table->string('desa');
            $table->string('nomor_legalitas')->nullable();
            $table->boolean('terverifikasi')->default(false);
            $table->string('foto_profil')->nullable();
            $table->text('deskripsi')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelompok_tani');
    }
};
