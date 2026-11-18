<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanan_adopsi', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pesanan')->unique();
            $table->foreignId('donatur_id')->constrained('pengguna')->restrictOnDelete();
            $table->foreignId('proyek_id')->constrained('proyek')->restrictOnDelete();
            $table->unsignedInteger('jumlah_bibit');
            $table->unsignedBigInteger('harga_per_bibit');
            $table->unsignedBigInteger('total_bayar');
            $table->enum('status', ['menunggu_pembayaran', 'terkonfirmasi', 'dibatalkan', 'kedaluwarsa'])->default('menunggu_pembayaran');
            $table->dateTime('batas_bayar');
            $table->timestamp('dikonfirmasi_pada')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan_adopsi');
    }
};
