<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pohon extends Model
{
    protected $table = 'pohon';

    public const UPDATED_AT = null;

    protected $fillable = [
        'kode_pohon',
        'pesanan_id',
        'donatur_id',
        'proyek_id',
        'jenis_pohon_id',
        'status_kesehatan',
        'latitude',
        'longitude',
        'tanggal_tanam',
    ];

    /**
     * Terbitkan kode pohon untuk sebuah pesanan adopsi. Setiap bibit yang
     * diadopsi menghasilkan satu baris pohon agar bisa dilaporkan di lapangan.
     * Idempoten: hanya menambah sampai jumlahnya sama dengan bibit pesanan.
     */
    public static function issueForOrder(PesananAdopsi $order, ?int $count = null): int
    {
        $count ??= (int) $order->jumlah_bibit;
        $existing = static::query()->where('pesanan_id', $order->id)->count();
        $target = max(0, $count - $existing);

        if ($target === 0) {
            return 0;
        }

        $jenisPohonId = $order->proyek?->jenis_pohon_id;
        $created = 0;
        $seq = $existing;

        while ($created < $target) {
            $seq++;
            $kode = sprintf('ORD-%d-%03d', $order->id, $seq);

            if (static::query()->where('kode_pohon', $kode)->exists()) {
                continue;
            }

            $pohon = new static();
            $pohon->timestamps = false;
            $pohon->setAttribute('kode_pohon', $kode);
            $pohon->setAttribute('pesanan_id', $order->id);
            $pohon->setAttribute('donatur_id', $order->donatur_id);
            $pohon->setAttribute('proyek_id', $order->proyek_id);
            $pohon->setAttribute('jenis_pohon_id', $jenisPohonId);
            $pohon->setAttribute('status_kesehatan', 'belum_ditanam');
            $pohon->save();

            $created++;
        }

        return $created;
    }

    protected $casts = [
        'tanggal_tanam' => 'date',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(PesananAdopsi::class, 'pesanan_id');
    }

    public function donatur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'donatur_id');
    }

    public function proyek(): BelongsTo
    {
        return $this->belongsTo(Proyek::class, 'proyek_id');
    }

    public function jenisPohon(): BelongsTo
    {
        return $this->belongsTo(JenisPohon::class, 'jenis_pohon_id');
    }

    public function laporanLapangan(): HasMany
    {
        return $this->hasMany(LaporanLapangan::class, 'pohon_id');
    }

    public function fotoGeotag(): HasMany
    {
        return $this->hasMany(FotoGeotag::class, 'pohon_id');
    }
}
