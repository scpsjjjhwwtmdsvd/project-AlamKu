<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proyek extends Model
{
    protected $table = 'proyek';

    public const UPDATED_AT = null;

    protected $fillable = [
        'lokasi_hutan_id',
        'kelompok_tani_id',
        'jenis_pohon_id',
        'judul',
        'deskripsi',
        'target_ekologi',
        'biaya_per_bibit',
        'kuota_bibit',
        'bibit_dipesan',
        'tanggal_mulai_tanam',
        'tanggal_selesai_tanam',
        'status',
    ];

    protected $casts = [
        'biaya_per_bibit' => 'integer',
        'kuota_bibit' => 'integer',
        'bibit_dipesan' => 'integer',
        'tanggal_mulai_tanam' => 'date',
        'tanggal_selesai_tanam' => 'date',
        'created_at' => 'datetime',
    ];

    public function lokasiHutan(): BelongsTo
    {
        return $this->belongsTo(LokasiHutan::class, 'lokasi_hutan_id');
    }

    public function kelompokTani(): BelongsTo
    {
        return $this->belongsTo(KelompokTani::class, 'kelompok_tani_id');
    }

    public function jenisPohon(): BelongsTo
    {
        return $this->belongsTo(JenisPohon::class, 'jenis_pohon_id');
    }

    public function alokasiDana(): HasMany
    {
        return $this->hasMany(AlokasiDana::class, 'proyek_id');
    }

    public function pohon(): HasMany
    {
        return $this->hasMany(Pohon::class, 'proyek_id');
    }

    public function pesananAdopsi(): HasMany
    {
        return $this->hasMany(PesananAdopsi::class, 'proyek_id');
    }

    public function laporanLapangan(): HasMany
    {
        return $this->hasMany(LaporanLapangan::class, 'proyek_id');
    }
}
