<?php

namespace App\Http\Controllers;

use App\Models\JenisPohon;

class JenisPohonController extends CrudController
{
    protected function modelClass(): string
    {
        return JenisPohon::class;
    }

    protected function routePrefix(): string
    {
        return 'jenis-pohon';
    }

    protected function pageTitle(): string
    {
        return 'Jenis Pohon';
    }

    protected function columns(): array
    {
        return [
            ['label' => 'ID', 'key' => 'id'],
            ['label' => 'Nama Lokal', 'key' => 'nama_lokal'],
            ['label' => 'Nama Ilmiah', 'key' => 'nama_ilmiah'],
            ['label' => 'Serapan Karbon/Tahun', 'key' => 'serapan_karbon_per_tahun'],
            ['label' => 'Dibuat', 'key' => 'created_at'],
        ];
    }

    protected function fields(): array
    {
        return [
            [
                'name' => 'nama_lokal',
                'label' => 'Nama Lokal',
                'type' => 'text',
                'rules' => ['required', 'string', 'max:255'],
            ],
            [
                'name' => 'nama_ilmiah',
                'label' => 'Nama Ilmiah',
                'type' => 'text',
                'nullable' => true,
                'rules' => ['nullable', 'string', 'max:255'],
            ],
            [
                'name' => 'serapan_karbon_per_tahun',
                'label' => 'Serapan Karbon per Tahun',
                'type' => 'number',
                'step' => '0.01',
                'rules' => ['required', 'numeric', 'min:0'],
            ],
        ];
    }
}
