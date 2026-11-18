
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

            $table->foreignId('pesanan_id')
                  ->constrained('pesanan_adopsi')
                  ->restrictOnDelete();

            $table->foreignId('donatur_id')
                  ->constrained('pengguna')
                  ->restrictOnDelete();

            $table->foreignId('proyek_id')
                  ->constrained('proyek')
                  ->restrictOnDelete();

            $table->foreignId('jenis_pohon_id')
                  ->constrained('jenis_pohon')
                  ->restrictOnDelete();

            $table->enum('status_kesehatan', [
                'belum_ditanam',
                'tumbuh_baik',
                'perlu_penyulaman',
                'mati'
            ])->default('belum_ditanam');

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->date('tanggal_tanam')->nullable();

            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pohon');
    }
};
