<?php

use App\Models\PesananAdopsi;
use App\Models\Pohon;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Isi data pohon untuk pesanan adopsi yang sudah terkonfirmasi tetapi
     * belum memiliki baris pohon (sebelum perbaikan pembuatan otomatis).
     */
    public function up(): void
    {
        PesananAdopsi::query()
            ->where('status', 'terkonfirmasi')
            ->with('proyek')
            ->each(function (PesananAdopsi $order): void {
                Pohon::issueForOrder($order);
            });
    }

    public function down(): void
    {
        // Data pohon hasil isian tidak dihapus otomatis.
    }
};
