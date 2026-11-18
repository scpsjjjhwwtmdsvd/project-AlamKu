<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LaporanLapangan extends Model
{
    protected $table = 'laporan_lapangan';

    protected $fillable = [
        'pohon_id',
        'proyek_id',
        'pengguna_id',
        'judul',
        'deskripsi',
        'kondisi',
        'tinggi_cm',
        'diameter_cm',
        'tanggal_laporan',
        'status',
        'catatan_verifikasi',
    ];

    protected $casts = [
        'tinggi_cm' => 'integer',
        'diameter_cm' => 'decimal:2',
        'tanggal_laporan' => 'date',
    ];

    public function pohon(): BelongsTo
    {
        return $this->belongsTo(Pohon::class, 'pohon_id');
    }

    public function proyek(): BelongsTo
    {
        return $this->belongsTo(Proyek::class, 'proyek_id');
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }

    public function fotoGeotag(): HasMany
    {
        return $this->hasMany(FotoGeotag::class, 'laporan_lapangan_id');
    }
}
