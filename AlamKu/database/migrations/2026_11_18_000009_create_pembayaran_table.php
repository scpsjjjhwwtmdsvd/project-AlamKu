<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->constrained('pesanan_adopsi')->restrictOnDelete();
            $table->enum('metode', ['qris', 'virtual_account', 'dompet_digital']);
            $table->string('penyedia');
            $table->string('referensi_penyedia')->unique();
            $table->unsignedBigInteger('jumlah');
            $table->enum('status', ['pending', 'berhasil', 'gagal', 'kedaluwarsa'])->default('pending');
            $table->timestamp('dibayar_pada')->nullable();
            $table->json('data_respons_gateway')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
