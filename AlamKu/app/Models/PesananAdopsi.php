<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PesananAdopsi extends Model
{
    protected $table = 'pesanan_adopsi';

    public const UPDATED_AT = null;

    protected $fillable = [
        'kode_pesanan',
        'donatur_id',
        'proyek_id',
        'jumlah_bibit',
        'harga_per_bibit',
        'total_bayar',
        'status',
        'batas_bayar',
        'dikonfirmasi_pada',
    ];

    protected $casts = [
        'jumlah_bibit' => 'integer',
        'harga_per_bibit' => 'integer',
        'total_bayar' => 'integer',
        'batas_bayar' => 'datetime',
        'dikonfirmasi_pada' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function donatur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'donatur_id');
    }

    public function proyek(): BelongsTo
    {
        return $this->belongsTo(Proyek::class, 'proyek_id');
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'pesanan_id');
    }
}
