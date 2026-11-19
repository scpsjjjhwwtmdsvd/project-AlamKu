<?php

namespace App\Http\Controllers;

use App\Models\PesananAdopsi;
use App\Models\Proyek;

class PesananAdopsiController extends CrudController
{
    protected function modelClass(): string
    {
        return PesananAdopsi::class;
    }

    protected function routePrefix(): string
    {
        return 'pesanan-adopsi';
    }

    protected function pageTitle(): string
    {
        return 'Pesanan Adopsi';
    }

    protected function relations(): array
    {
        return ['donatur', 'proyek'];
    }

    protected function columns(): array
    {
        return [
            ['label' => 'ID', 'key' => 'id'],
            ['label' => 'Kode Pesanan', 'key' => 'kode_pesanan'],
            ['label' => 'Donatur', 'relation' => 'donatur', 'label_key' => 'name'],
            ['label' => 'Proyek', 'relation' => 'proyek', 'label_key' => 'judul'],
            ['label' => 'Jumlah Bibit', 'key' => 'jumlah_bibit'],
            ['label' => 'Harga/Bibit', 'key' => 'harga_per_bibit'],
            ['label' => 'Total Bayar', 'key' => 'total_bayar'],
            ['label' => 'Status', 'key' => 'status', 'humanize' => true],
            ['label' => 'Batas Bayar', 'key' => 'batas_bayar'],
        ];
    }

    protected function fields(): array
    {
        return [
            [
                'name' => 'kode_pesanan',
                'label' => 'Kode Pesanan',
                'type' => 'text',
                'unique' => true,
                'rules' => ['required', 'string', 'max:255'],
            ],
            [
                'name' => 'donatur_id',
                'label' => 'Donatur',
                'type' => 'select',
                'rules' => ['required', 'integer', 'exists:pengguna,id'],
                'options' => fn (): array => $this->userOptions(),
            ],
            [
                'name' => 'proyek_id',
                'label' => 'Proyek',
                'type' => 'select',
                'rules' => ['required', 'integer', 'exists:proyek,id'],
                'options' => fn (): array => Proyek::query()
                    ->orderBy('judul')
                    ->pluck('judul', 'id')
                    ->all(),
            ],
            [
                'name' => 'jumlah_bibit',
                'label' => 'Jumlah Bibit',
                'type' => 'number',
                'rules' => ['required', 'integer', 'min:1'],
            ],
            [
                'name' => 'harga_per_bibit',
                'label' => 'Harga per Bibit',
                'type' => 'number',
                'rules' => ['required', 'integer', 'min:0'],
            ],
            [
                'name' => 'total_bayar',
                'label' => 'Total Bayar',
                'type' => 'number',
                'rules' => ['required', 'integer', 'min:0'],
            ],
            [
                'name' => 'status',
                'label' => 'Status',
                'type' => 'select',
                'default' => 'menunggu_pembayaran',
                'rules' => ['required', 'string', 'in:menunggu_pembayaran,terkonfirmasi,dibatalkan,kedaluwarsa'],
                'options' => [
                    'menunggu_pembayaran' => 'Menunggu Pembayaran',
                    'terkonfirmasi' => 'Terkonfirmasi',
                    'dibatalkan' => 'Dibatalkan',
                    'kedaluwarsa' => 'Kedaluwarsa',
                ],
            ],
            [
                'name' => 'batas_bayar',
                'label' => 'Batas Bayar',
                'type' => 'datetime-local',
                'rules' => ['required', 'date'],
            ],
            [
                'name' => 'dikonfirmasi_pada',
                'label' => 'Dikonfirmasi Pada',
                'type' => 'datetime-local',
                'nullable' => true,
                'rules' => ['nullable', 'date'],
            ],
        ];
    }
}
