<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

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

        $order->loadMissing('proyek.lokasiHutan', 'proyek.jenisPohon');

        $proyek = $order->proyek;
        $prefix = static::prefixForProject($proyek);

        // Hitung hanya pohon milik proyek ini, supaya pohon dari proyek lain
        // yang tidak sengaja tertaut ke pesanan tidak mengurangi kuota.
        $existing = static::query()
            ->where('pesanan_id', $order->id)
            ->where('proyek_id', $order->proyek_id)
            ->count();

        $target = max(0, $count - $existing);

        if ($target === 0) {
            return 0;
        }

        $jenisPohonId = $proyek?->jenis_pohon_id;
        $seq = static::nextSequence($prefix);
        $created = 0;

        while ($created < $target) {
            $seq++;
            $kode = sprintf('%s-%03d', $prefix, $seq);

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

    /**
     * Prefix kode pohon untuk sebuah proyek, mis. CKL-MHN.
     * Diambil dari kolom kode_prefix; jika kosong, diturunkan dari
     * nama lokasi dan nama jenis pohon.
     */
    public static function prefixForProject(?Proyek $proyek): string
    {
        if (! $proyek) {
            return 'PHN';
        }

        if (filled($proyek->kode_prefix)) {
            return Str::upper($proyek->kode_prefix);
        }

        $kodeLokasi = static::codeFromName($proyek->lokasiHutan?->nama_area_konservasi);
        $kodeJenis = static::codeFromName($proyek->jenisPohon?->nama_lokal);
        $prefix = trim($kodeLokasi.'-'.$kodeJenis, '-');

        return $prefix !== '' ? $prefix : 'PHN';
    }

    private static function codeFromName(?string $name): string
    {
        if (blank($name)) {
            return '';
        }

        $words = preg_split('/\s+/', trim($name)) ?: [];
        $word = end($words) ?: $name;
        $word = Str::upper(preg_replace('/[^A-Za-z]/', '', $word) ?? '');
        $consonants = preg_replace('/[AEIOU]/', '', $word) ?? '';
        $code = $consonants !== '' ? $consonants : $word;

        return substr($code, 0, 3);
    }

    private static function nextSequence(string $prefix): int
    {
        $codes = static::query()
            ->where('kode_pohon', 'like', $prefix.'-%')
            ->pluck('kode_pohon');

        $max = 0;
        $prefixLength = strlen($prefix) + 1;

        foreach ($codes as $code) {
            $suffix = substr($code, $prefixLength);

            if (ctype_digit($suffix)) {
                $max = max($max, (int) $suffix);
            }
        }

        return $max;
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
