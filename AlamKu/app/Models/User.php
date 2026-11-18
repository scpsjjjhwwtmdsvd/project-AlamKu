<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'phone', 'password', 'role'])]
#[Hidden(['password'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'pengguna';

    public const UPDATED_AT = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'created_at' => 'datetime',
        ];
    }

    public function kelompokTani(): HasOne
    {
        return $this->hasOne(KelompokTani::class, 'pengguna_id');
    }

    public function pesananAdopsi(): HasMany
    {
        return $this->hasMany(PesananAdopsi::class, 'donatur_id');
    }

    public function laporanLapangan(): HasMany
    {
        return $this->hasMany(LaporanLapangan::class, 'pengguna_id');
    }

    public function logPerubahan(): HasMany
    {
        return $this->hasMany(LogPerubahan::class, 'pengguna_id');
    }
}
