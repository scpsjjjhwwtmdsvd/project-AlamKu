<?php

namespace App\Http\Controllers;

use App\Models\KelompokTani;
use App\Models\LokasiHutan;

class LokasiHutanController extends CrudController
{
    protected function modelClass(): string
    {
        return LokasiHutan::class;
    }

    protected function routePrefix(): string
    {
        return 'lokasi-hutan';
    }

    protected function pageTitle(): string
    {
        return 'Lokasi Hutan';
    }

    protected function relations(): array
    {
        return ['kelompokTani'];
    }

    protected function columns(): array
    {
        return [
            ['label' => 'ID', 'key' => 'id'],
            ['label' => 'Area Konservasi', 'key' => 'nama_area_konservasi'],
            ['label' => 'Kelompok Tani', 'relation' => 'kelompokTani', 'label_key' => 'nama_kelompok'],
            ['label' => 'Provinsi', 'key' => 'provinsi'],
            ['label' => 'Kabupaten', 'key' => 'kabupaten'],
            ['label' => 'Latitude', 'key' => 'latitude'],
            ['label' => 'Longitude', 'key' => 'longitude'],
            ['label' => 'Luas (ha)', 'key' => 'luas_hektar'],
        ];
    }

    protected function fields(): array
    {
        return [
            [
                'name' => 'kelompok_tani_id',
                'label' => 'Kelompok Tani',
                'type' => 'select',
                'rules' => ['required', 'integer', 'exists:kelompok_tani,id'],
                'options' => fn (): array => KelompokTani::query()
                    ->orderBy('nama_kelompok')
                    ->pluck('nama_kelompok', 'id')
                    ->all(),
            ],
            [
                'name' => 'nama_area_konservasi',
                'label' => 'Nama Area Konservasi',
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
                'name' => 'latitude',
                'label' => 'Latitude',
                'type' => 'number',
                'step' => '0.0000001',
                'rules' => ['required', 'numeric', 'between:-90,90'],
            ],
            [
                'name' => 'longitude',
                'label' => 'Longitude',
                'type' => 'number',
                'step' => '0.0000001',
                'rules' => ['required', 'numeric', 'between:-180,180'],
            ],
            [
                'name' => 'luas_hektar',
                'label' => 'Luas (hektar)',
                'type' => 'number',
                'step' => '0.01',
                'nullable' => true,
                'rules' => ['nullable', 'numeric', 'min:0'],
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
