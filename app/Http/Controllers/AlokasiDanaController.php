<?php

namespace App\Http\Controllers;

use App\Models\AlokasiDana;
use App\Models\Proyek;

class AlokasiDanaController extends CrudController
{
    protected function modelClass(): string
    {
        return AlokasiDana::class;
    }

    protected function routePrefix(): string
    {
        return 'alokasi-dana';
    }

    protected function pageTitle(): string
    {
        return 'Alokasi Dana';
    }

    protected function relations(): array
    {
        return ['proyek'];
    }

    protected function columns(): array
    {
        return [
            ['label' => 'ID', 'key' => 'id'],
            ['label' => 'Proyek', 'relation' => 'proyek', 'label_key' => 'judul'],
            ['label' => 'Kategori', 'key' => 'kategori'],
            ['label' => 'Rencana', 'key' => 'rencana'],
            ['label' => 'Realisasi', 'key' => 'realisasi'],
            ['label' => 'Keterangan', 'key' => 'keterangan'],
            ['label' => 'Dibuat', 'key' => 'created_at'],
        ];
    }

    protected function fields(): array
    {
        return [
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
                'name' => 'kategori',
                'label' => 'Kategori',
                'type' => 'text',
                'rules' => ['required', 'string', 'max:255'],
            ],
            [
                'name' => 'rencana',
                'label' => 'Rencana',
                'type' => 'number',
                'rules' => ['required', 'integer', 'min:0'],
            ],
            [
                'name' => 'realisasi',
                'label' => 'Realisasi',
                'type' => 'number',
                'default' => 0,
                'rules' => ['nullable', 'integer', 'min:0'],
            ],
            [
                'name' => 'keterangan',
                'label' => 'Keterangan',
                'type' => 'textarea',
                'nullable' => true,
                'rules' => ['nullable', 'string'],
                'rows' => 3,
            ],
        ];
    }
}
