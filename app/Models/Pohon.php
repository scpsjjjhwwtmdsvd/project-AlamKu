<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pohon extends Model
{
    protected $table = 'pohon';

    protected $fillable = [
        'kode_pohon',
        'proyek_id',
        'jenis_pohon_id',
        'lokasi_hutan_id',
        'kelompok_tani_id',
        'tinggi_cm',
        'diameter_cm',
        'tanggal_tanam',
        'status',
        'latitude',
        'longitude',
        'catatan',
        'foto',
    ];

    protected $casts = [
        'tinggi_cm' => 'integer',
        'diameter_cm' => 'decimal:2',
        'tanggal_tanam' => 'date',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function proyek(): BelongsTo
    {
        return $this->belongsTo(Proyek::class, 'proyek_id');
    }

    public function jenisPohon(): BelongsTo
    {
        return $this->belongsTo(JenisPohon::class, 'jenis_pohon_id');
    }

    public function lokasiHutan(): BelongsTo
    {
        return $this->belongsTo(LokasiHutan::class, 'lokasi_hutan_id');
    }

    public function kelompokTani(): BelongsTo
    {
        return $this->belongsTo(KelompokTani::class, 'kelompok_tani_id');
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
