<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisPohon extends Model
{
    protected $table = 'jenis_pohon';

    public const UPDATED_AT = null;

    protected $fillable = [
        'nama_lokal',
        'nama_ilmiah',
        'serapan_karbon_per_tahun',
    ];

    protected $casts = [
        'serapan_karbon_per_tahun' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function proyek(): HasMany
    {
        return $this->hasMany(Proyek::class, 'jenis_pohon_id');
    }

    public function pohon(): HasMany
    {
        return $this->hasMany(Pohon::class, 'jenis_pohon_id');
    }
}
