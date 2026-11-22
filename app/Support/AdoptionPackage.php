<?php

namespace App\Support;

final class AdoptionPackage
{
    /**
     * Minimum nilai transaksi adopsi, bukan harga satu bibit.
     */
    public const MINIMUM_TRANSACTION = 50000;

    /**
     * Paket adopsi bibit dengan jumlah dan harga tetap.
     *
     * @var array<string, array{name: string, trees: int, price: int}>
     */
    private const PACKAGES = [
        'starter' => ['name' => 'Starter', 'trees' => 2, 'price' => 50000],
        'growth' => ['name' => 'Growth', 'trees' => 5, 'price' => 100000],
        'forest' => ['name' => 'Forest', 'trees' => 10, 'price' => 200000],
        'guardian' => ['name' => 'Guardian', 'trees' => 25, 'price' => 500000],
    ];

    /**
     * Pembagian dana untuk setiap transaksi adopsi.
     *
     * @var array<int, array{label: string, percent: int}>
     */
    private const ALLOCATIONS = [
        ['label' => 'Bibit dan penanaman', 'percent' => 50],
        ['label' => 'Pemeliharaan pohon', 'percent' => 20],
        ['label' => 'Operasional mitra', 'percent' => 15],
        ['label' => 'Pengembangan website', 'percent' => 10],
        ['label' => 'Dana cadangan', 'percent' => 5],
    ];

    /**
     * @return array<string, array{key: string, name: string, trees: int, price: int}>
     */
    public static function all(): array
    {
        $packages = [];

        foreach (self::PACKAGES as $key => $package) {
            $packages[$key] = ['key' => $key] + $package;
        }

        return $packages;
    }

    /**
     * @return array{key: string, name: string, trees: int, price: int}|null
     */
    public static function find(?string $key): ?array
    {
        if ($key === null || ! isset(self::PACKAGES[$key])) {
            return null;
        }

        return ['key' => $key] + self::PACKAGES[$key];
    }

    /**
     * Cocokkan jumlah bibit dan nominal untuk mengenali paket dari pesanan lama.
     *
     * @return array{key: string, name: string, trees: int, price: int}|null
     */
    public static function match(int $trees, int $price): ?array
    {
        foreach (self::PACKAGES as $key => $package) {
            if ($package['trees'] === $trees && $package['price'] === $price) {
                return ['key' => $key] + $package;
            }
        }

        return null;
    }

    /**
     * @return array<int, array{label: string, percent: int}>
     */
    public static function allocationRules(): array
    {
        return self::ALLOCATIONS;
    }

    /**
     * Hitung nominal tiap pos. Sisa pembulatan masuk ke pos terakhir agar
     * jumlahnya selalu sama dengan nilai transaksi.
     *
     * @return array<int, array{label: string, percent: int, amount: int}>
     */
    public static function allocation(int $total): array
    {
        $allocation = [];
        $allocated = 0;
        $lastIndex = count(self::ALLOCATIONS) - 1;

        foreach (self::ALLOCATIONS as $index => $item) {
            $amount = $index === $lastIndex
                ? $total - $allocated
                : (int) round($total * $item['percent'] / 100);

            $allocated += $amount;
            $allocation[] = $item + ['amount' => $amount];
        }

        return $allocation;
    }
}
