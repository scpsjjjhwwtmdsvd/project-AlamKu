<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';

    public const UPDATED_AT = null;

    protected $fillable = [
        'pesanan_id',
        'metode',
        'penyedia',
        'referensi_penyedia',
        'jumlah',
        'status',
        'dibayar_pada',
        'data_respons_gateway',
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'dibayar_pada' => 'datetime',
        'data_respons_gateway' => 'array',
        'created_at' => 'datetime',
    ];

    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(PesananAdopsi::class, 'pesanan_id');
    }
}
