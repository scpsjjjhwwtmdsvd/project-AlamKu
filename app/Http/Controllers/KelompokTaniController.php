<?php

namespace App\Http\Controllers;

use App\Models\KelompokTani;

class KelompokTaniController extends CrudController
{
    protected function modelClass(): string
    {
        return KelompokTani::class;
    }

    protected function routePrefix(): string
    {
        return 'kelompok-tani';
    }

    protected function pageTitle(): string
    {
        return 'Kelompok Tani';
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
            ['label' => 'Foto', 'key' => 'foto_profil', 'image' => true],
            ['label' => 'Nama Kelompok', 'key' => 'nama_kelompok'],
            ['label' => 'Provinsi', 'key' => 'provinsi'],
            ['label' => 'Kabupaten', 'key' => 'kabupaten'],
            ['label' => 'Desa', 'key' => 'desa'],
            ['label' => 'Terverifikasi', 'key' => 'terverifikasi'],
        ];
    }

    protected function fields(): array
    {
        return [
            [
                'name' => 'pengguna_id',
                'label' => 'Pengguna',
                'type' => 'search-select',
                'rules' => ['required', 'integer', 'exists:pengguna,id'],
                'unique' => true,
                'placeholder' => 'Ketik nama pengguna...',
                'help' => 'Ketik nama atau pilih dari daftar pengguna.',
                'options' => fn (): array => $this->userOptions(),
            ],
            [
                'name' => 'nama_kelompok',
                'label' => 'Nama Kelompok',
                'type' => 'text',
                'rules' => ['required', 'string', 'max:255'],
            ],
            [
                'name' => 'provinsi',
                'label' => 'Provinsi',
                'type' => 'text',
                'rules' => ['required', 'string', 'max:255'],
            ],
            [
                'name' => 'kabupaten',
                'label' => 'Kabupaten',
                'type' => 'text',
                'rules' => ['required', 'string', 'max:255'],
            ],
            [
                'name' => 'desa',
                'label' => 'Desa',
                'type' => 'text',
                'rules' => ['required', 'string', 'max:255'],
            ],
            [
                'name' => 'nomor_legalitas',
                'label' => 'Nomor Legalitas',
                'type' => 'text',
                'nullable' => true,
                'rules' => ['nullable', 'string', 'max:255'],
            ],
            [
                'name' => 'terverifikasi',
                'label' => 'Terverifikasi',
                'type' => 'checkbox',
                'default' => false,
                'rules' => ['boolean'],
            ],
            [
                'name' => 'foto_profil',
                'label' => 'Foto Profil Kelompok',
                'type' => 'file',
                'nullable' => true,
                'rules' => ['nullable', 'image', 'max:4096'],
                'help' => 'Unggah JPG, PNG, atau WEBP maksimal 4 MB. Foto membantu membedakan kelompok dengan nama serupa.',
            ],
            [
                'name' => 'deskripsi',
                'label' => 'Deskripsi',
                'type' => 'textarea',
                'nullable' => true,
                'rules' => ['nullable', 'string'],
                'rows' => 4,
            ],
        ];
    }
}
