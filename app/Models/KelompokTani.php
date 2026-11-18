<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KelompokTani extends Model
{
    protected $table = 'kelompok_tani';

    public const UPDATED_AT = null;

    protected $fillable = [
        'pengguna_id',
        'nama_kelompok',
        'provinsi',
        'kabupaten',
        'desa',
        'nomor_legalitas',
        'terverifikasi',
        'foto_profil',
        'deskripsi',
    ];

    protected $casts = [
        'terverifikasi' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }

    public function lokasiHutan(): HasMany
    {
        return $this->hasMany(LokasiHutan::class, 'kelompok_tani_id');
    }

    public function proyek(): HasMany
    {
        return $this->hasMany(Proyek::class, 'kelompok_tani_id');
    }
}
