<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\PesananAdopsi;

class PembayaranController extends CrudController
{
    protected function modelClass(): string
    {
        return Pembayaran::class;
    }

    protected function routePrefix(): string
    {
        return 'pembayaran';
    }

    protected function pageTitle(): string
    {
        return 'Pembayaran';
    }

    protected function relations(): array
    {
        return ['pesanan'];
    }

    protected function columns(): array
    {
        return [
            ['label' => 'ID', 'key' => 'id'],
            ['label' => 'Kode Pesanan', 'relation' => 'pesanan', 'label_key' => 'kode_pesanan'],
            ['label' => 'Metode', 'key' => 'metode', 'humanize' => true],
            ['label' => 'Penyedia', 'key' => 'penyedia'],
            ['label' => 'Referensi', 'key' => 'referensi_penyedia'],
            ['label' => 'Jumlah', 'key' => 'jumlah'],
            ['label' => 'Status', 'key' => 'status', 'humanize' => true],
            ['label' => 'Dibayar Pada (WIB)', 'key' => 'dibayar_pada'],
        ];
    }

    protected function fields(): array
    {
        return [
            [
                'name' => 'pesanan_id',
                'label' => 'Pesanan Adopsi',
                'type' => 'select',
                'rules' => ['required', 'integer', 'exists:pesanan_adopsi,id'],
                'options' => fn (): array => PesananAdopsi::query()
                    ->orderByDesc('id')
                    ->pluck('kode_pesanan', 'id')
                    ->all(),
            ],
            [
                'name' => 'metode',
                'label' => 'Metode',
                'type' => 'select',
                'rules' => ['required', 'string', 'in:qris,virtual_account,dompet_digital'],
                'options' => [
                    'qris' => 'QRIS',
                    'virtual_account' => 'Virtual Account',
                    'dompet_digital' => 'Dompet Digital',
                ],
            ],
            [
                'name' => 'penyedia',
                'label' => 'Penyedia',
                'type' => 'text',
                'rules' => ['required', 'string', 'max:255'],
            ],
            [
                'name' => 'referensi_penyedia',
                'label' => 'Referensi Penyedia',
                'type' => 'text',
                'unique' => true,
                'rules' => ['required', 'string', 'max:255'],
            ],
            [
                'name' => 'jumlah',
                'label' => 'Jumlah',
                'type' => 'number',
                'rules' => ['required', 'integer', 'min:0'],
            ],
            [
                'name' => 'status',
                'label' => 'Status',
                'type' => 'select',
                'default' => 'pending',
                'rules' => ['required', 'string', 'in:pending,berhasil,gagal,kedaluwarsa'],
                'options' => [
                    'pending' => 'Pending',
                    'berhasil' => 'Berhasil',
                    'gagal' => 'Gagal',
                    'kedaluwarsa' => 'Kedaluwarsa',
                ],
            ],
            [
                'name' => 'dibayar_pada',
                'label' => 'Dibayar Pada (WIB)',
                'type' => 'datetime-local',
                'nullable' => true,
                'rules' => ['nullable', 'date'],
                'help' => 'Waktu mengikuti zona Asia/Jakarta (WIB).',
            ],
        ];
    }
}
