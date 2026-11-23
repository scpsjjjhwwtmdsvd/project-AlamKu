<?php

namespace App\Support;

final class PaymentMethod
{
    /**
     * Nilai metode mengikuti enum kolom pembayaran.metode.
     *
     * @var array<string, array{label: string, hint: string}>
     */
    private const CHANNELS = [
        'qris' => [
            'label' => 'QRIS',
            'hint' => 'Pindai kode QR simulasi',
        ],
        'virtual_account' => [
            'label' => 'M-Banking',
            'hint' => 'Transfer bank',
        ],
        'dompet_digital' => [
            'label' => 'E-Wallet',
            'hint' => 'Bayar lewat dompet digital',
        ],
    ];

    /**
     * @var array<int, string>
     */
    private const BANKS = [
        'BCA',
        'BRI',
        'BNI',
        'Mandiri',
        'CIMB Niaga',
    ];

    /**
     * @var array<int, string>
     */
    private const WALLETS = [
        'GoPay',
        'OVO',
        'DANA',
        'ShopeePay',
        'LinkAja',
    ];

    /**
     * @return array<string, array{label: string, hint: string}>
     */
    public static function channels(): array
    {
        return self::CHANNELS;
    }

    /**
     * @return array<int, string>
     */
    public static function banks(): array
    {
        return self::BANKS;
    }

    /**
     * @return array<int, string>
     */
    public static function wallets(): array
    {
        return self::WALLETS;
    }

    public static function label(?string $method): ?string
    {
        if ($method === null) {
            return null;
        }

        return self::CHANNELS[$method]['label'] ?? ucfirst(str_replace('_', ' ', $method));
    }
}
