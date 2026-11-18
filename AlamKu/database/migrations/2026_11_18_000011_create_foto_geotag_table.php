<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('foto_geotag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_lapangan_id')->nullable()->constrained('laporan_lapangan')->cascadeOnDelete();
            $table->foreignId('pohon_id')->nullable()->constrained('pohon')->cascadeOnDelete();
            $table->string('path');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('altitude', 8, 2)->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foto_geotag');
    }
};
