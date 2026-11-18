<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FotoGeotag extends Model
{
    protected $table = 'foto_geotag';

    protected $fillable = [
        'laporan_lapangan_id',
        'pohon_id',
        'path',
        'latitude',
        'longitude',
        'altitude',
        'captured_at',
        'keterangan',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'altitude' => 'decimal:2',
        'captured_at' => 'datetime',
    ];

    public function laporanLapangan(): BelongsTo
    {
        return $this->belongsTo(LaporanLapangan::class, 'laporan_lapangan_id');
    }

    public function pohon(): BelongsTo
    {
        return $this->belongsTo(Pohon::class, 'pohon_id');
    }
}
