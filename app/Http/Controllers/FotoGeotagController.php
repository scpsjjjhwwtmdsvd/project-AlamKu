<?php

namespace App\Http\Controllers;

use App\Models\FotoGeotag;
use App\Models\LaporanLapangan;
use App\Models\Pohon;

class FotoGeotagController extends CrudController
{
    protected function modelClass(): string
    {
        return FotoGeotag::class;
    }

    protected function routePrefix(): string
    {
        return 'foto-geotag';
    }

    protected function pageTitle(): string
    {
        return 'Foto Geotag';
    }

    protected function relations(): array
    {
        return ['laporanLapangan', 'pohon'];
    }

    protected function columns(): array
    {
        return [
            ['label' => 'ID', 'key' => 'id'],
            ['label' => 'Path', 'key' => 'path'],
            ['label' => 'Laporan', 'relation' => 'laporanLapangan', 'label_key' => 'judul'],
            ['label' => 'Pohon', 'relation' => 'pohon', 'label_key' => 'kode_pohon'],
            ['label' => 'Latitude', 'key' => 'latitude'],
            ['label' => 'Longitude', 'key' => 'longitude'],
            ['label' => 'Altitude', 'key' => 'altitude'],
            ['label' => 'Diambil', 'key' => 'captured_at'],
        ];
    }

    protected function fields(): array
    {
        return [
            [
                'name' => 'laporan_lapangan_id',
                'label' => 'Laporan Lapangan',
                'type' => 'select',
                'nullable' => true,
                'rules' => ['nullable', 'integer', 'exists:laporan_lapangan,id'],
                'options' => fn (): array => LaporanLapangan::query()
                    ->orderByDesc('id')
                    ->pluck('judul', 'id')
                    ->all(),
            ],
            [
                'name' => 'pohon_id',
                'label' => 'Pohon',
                'type' => 'select',
                'nullable' => true,
                'rules' => ['nullable', 'integer', 'exists:pohon,id'],
                'options' => fn (): array => Pohon::query()
                    ->orderBy('kode_pohon')
                    ->pluck('kode_pohon', 'id')
                    ->all(),
            ],
            [
                'name' => 'path',
                'label' => 'Path Foto',
                'type' => 'text',
                'rules' => ['required', 'string', 'max:2048'],
            ],
            [
                'name' => 'latitude',
                'label' => 'Latitude',
                'type' => 'number',
                'step' => '0.0000001',
                'nullable' => true,
                'rules' => ['nullable', 'numeric', 'between:-90,90'],
            ],
            [
                'name' => 'longitude',
                'label' => 'Longitude',
                'type' => 'number',
                'step' => '0.0000001',
                'nullable' => true,
                'rules' => ['nullable', 'numeric', 'between:-180,180'],
            ],
            [
                'name' => 'altitude',
                'label' => 'Altitude',
                'type' => 'number',
                'step' => '0.01',
                'nullable' => true,
                'rules' => ['nullable', 'numeric'],
            ],
            [
                'name' => 'captured_at',
                'label' => 'Diambil Pada',
                'type' => 'datetime-local',
                'nullable' => true,
                'rules' => ['nullable', 'date'],
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
