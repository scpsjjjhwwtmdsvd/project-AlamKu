<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LokasiHutan extends Model
{
    protected $table = 'lokasi_hutan';

    public const UPDATED_AT = null;

    protected $fillable = [
        'kelompok_tani_id',
        'nama_area_konservasi',
        'provinsi',
        'kabupaten',
        'latitude',
        'longitude',
        'luas_hektar',
        'deskripsi',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'luas_hektar' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function kelompokTani(): BelongsTo
    {
        return $this->belongsTo(KelompokTani::class, 'kelompok_tani_id');
    }

    public function proyek(): HasMany
    {
        return $this->hasMany(Proyek::class, 'lokasi_hutan_id');
    }

    public function pohon(): HasMany
    {
        return $this->hasMany(Pohon::class, 'lokasi_hutan_id');
    }
}
