<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlokasiDana extends Model
{
    protected $table = 'alokasi_dana';

    protected $fillable = [
        'proyek_id',
        'kategori',
        'rencana',
        'realisasi',
        'keterangan',
    ];

    protected $casts = [
        'rencana' => 'integer',
        'realisasi' => 'integer',
    ];

    public function proyek(): BelongsTo
    {
        return $this->belongsTo(Proyek::class, 'proyek_id');
    }
}
