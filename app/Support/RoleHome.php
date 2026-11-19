<?php

namespace App\Support;

final class RoleHome
{
    /**
     * Nama route dasbor untuk setiap peran pengguna.
     */
    public static function routeName(?string $role): string
    {
        return match ($role) {
            'admin' => 'admin.dashboard',
            'mitra' => 'mitra.dashboard',
            default => 'akun.dashboard',
        };
    }

    /**
     * Label ruang kerja untuk setiap peran pengguna.
     */
    public static function label(?string $role): string
    {
        return match ($role) {
            'admin' => 'Konsol Admin',
            'mitra' => 'Ruang Tani',
            default => 'Ruang Donatur',
        };
    }
}
