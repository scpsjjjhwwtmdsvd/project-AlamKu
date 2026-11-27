<?php

namespace App\Http\Controllers;

use App\Models\LogPerubahan;

class LogPerubahanController extends CrudController
{
    protected function modelClass(): string
    {
        return LogPerubahan::class;
    }

    protected function routePrefix(): string
    {
        return 'log-perubahan';
    }

    protected function pageTitle(): string
    {
        return 'Log Perubahan';
    }

    protected function relations(): array
    {
        return ['pengguna'];
    }

    protected function columns(): array
    {
        return [
            ['label' => 'ID', 'key' => 'id'],
            ['label' => 'Pengguna', 'relation' => 'pengguna', 'label_key' => 'name'],
            ['label' => 'Tabel', 'key' => 'tabel'],
            ['label' => 'Record ID', 'key' => 'record_id'],
            ['label' => 'Aksi', 'key' => 'aksi'],
            ['label' => 'IP Address', 'key' => 'ip_address'],
            ['label' => 'Waktu', 'key' => 'created_at'],
        ];
    }

    protected function fields(): array
    {
        return [
            [
                'name' => 'pengguna_id',
                'label' => 'Pengguna',
                'type' => 'search-select',
                'nullable' => true,
                'rules' => ['nullable', 'integer', 'exists:pengguna,id'],
                'placeholder' => 'Ketik nama pengguna...',
                'options' => fn (): array => $this->userOptions(),
            ],
            [
                'name' => 'tabel',
                'label' => 'Nama Tabel',
                'type' => 'text',
                'rules' => ['required', 'string', 'max:255'],
            ],
            [
                'name' => 'record_id',
                'label' => 'Record ID',
                'type' => 'number',
                'rules' => ['required', 'integer', 'min:1'],
            ],
            [
                'name' => 'aksi',
                'label' => 'Aksi',
                'type' => 'text',
                'rules' => ['required', 'string', 'max:255'],
            ],
            [
                'name' => 'data_lama',
                'label' => 'Data Lama (JSON)',
                'type' => 'textarea',
                'nullable' => true,
                'json' => true,
                'rules' => ['nullable', 'json'],
                'rows' => 5,
                'placeholder' => '{"nama":"nilai lama"}',
            ],
            [
                'name' => 'data_baru',
                'label' => 'Data Baru (JSON)',
                'type' => 'textarea',
                'nullable' => true,
                'json' => true,
                'rules' => ['nullable', 'json'],
                'rows' => 5,
                'placeholder' => '{"nama":"nilai baru"}',
            ],
            [
                'name' => 'ip_address',
                'label' => 'IP Address',
                'type' => 'text',
                'nullable' => true,
                'rules' => ['nullable', 'string', 'max:45'],
            ],
            [
                'name' => 'user_agent',
                'label' => 'User Agent',
                'type' => 'textarea',
                'nullable' => true,
                'rules' => ['nullable', 'string'],
                'rows' => 3,
            ],
        ];
    }
}
