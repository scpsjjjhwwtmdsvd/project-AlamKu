<?php

namespace App\Http\Controllers;

use App\Models\LaporanLapangan;
use App\Models\Pohon;
use App\Models\Proyek;

class LaporanLapanganController extends CrudController
{
    protected function modelClass(): string
    {
        return LaporanLapangan::class;
    }

    protected function routePrefix(): string
    {
        return 'laporan-lapangan';
    }

    protected function pageTitle(): string
    {
        return 'Laporan Lapangan';
    }

    protected function relations(): array
    {
        return ['proyek', 'pohon', 'pengguna'];
    }

    protected function columns(): array
    {
        return [
            ['label' => 'ID', 'key' => 'id'],
            ['label' => 'Judul', 'key' => 'judul'],
            ['label' => 'Proyek', 'relation' => 'proyek', 'label_key' => 'judul'],
            ['label' => 'Pohon', 'relation' => 'pohon', 'label_key' => 'kode_pohon'],
            ['label' => 'Pelapor', 'relation' => 'pengguna', 'label_key' => 'name'],
            ['label' => 'Kondisi', 'key' => 'kondisi'],
            ['label' => 'Tanggal Laporan', 'key' => 'tanggal_laporan', 'date' => true],
            ['label' => 'Status', 'key' => 'status', 'humanize' => true],
        ];
    }

    protected function fields(): array
    {
        return [
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
                'name' => 'pengguna_id',
                'label' => 'Pelapor',
                'type' => 'select',
                'nullable' => true,
                'rules' => ['nullable', 'integer', 'exists:pengguna,id'],
                'options' => fn (): array => $this->userOptions(),
            ],
            [
                'name' => 'judul',
                'label' => 'Judul',
                'type' => 'text',
                'rules' => ['required', 'string', 'max:255'],
            ],
            [
                'name' => 'deskripsi',
                'label' => 'Deskripsi',
                'type' => 'textarea',
                'nullable' => true,
                'rules' => ['nullable', 'string'],
                'rows' => 4,
            ],
            [
                'name' => 'kondisi',
                'label' => 'Kondisi',
                'type' => 'text',
                'default' => 'sehat',
                'rules' => ['required', 'string', 'max:255'],
            ],
            [
                'name' => 'tinggi_cm',
                'label' => 'Tinggi (cm)',
                'type' => 'number',
                'nullable' => true,
                'rules' => ['nullable', 'integer', 'min:0'],
            ],
            [
                'name' => 'diameter_cm',
                'label' => 'Diameter (cm)',
                'type' => 'number',
                'step' => '0.01',
                'nullable' => true,
                'rules' => ['nullable', 'numeric', 'min:0'],
            ],
            [
                'name' => 'tanggal_laporan',
                'label' => 'Tanggal Laporan',
                'type' => 'date',
                'rules' => ['required', 'date'],
            ],
            [
                'name' => 'status',
                'label' => 'Status',
                'type' => 'text',
                'default' => 'draft',
                'rules' => ['required', 'string', 'max:255'],
            ],
            [
                'name' => 'catatan_verifikasi',
                'label' => 'Catatan Verifikasi',
                'type' => 'textarea',
                'nullable' => true,
                'rules' => ['nullable', 'string'],
                'rows' => 3,
            ],
        ];
    }
}
