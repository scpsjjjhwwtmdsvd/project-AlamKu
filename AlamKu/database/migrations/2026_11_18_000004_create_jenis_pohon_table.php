<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_pohon', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lokal');
            $table->string('nama_ilmiah')->nullable();
            $table->decimal('serapan_karbon_per_tahun', 10, 2);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_pohon');
    }
};
