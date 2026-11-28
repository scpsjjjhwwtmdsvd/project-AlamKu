<?php

namespace App\Http\Controllers;

use App\Models\JenisPohon;
use App\Models\KelompokTani;
use App\Models\LokasiHutan;
use App\Models\Proyek;

class ProyekController extends CrudController
{
    protected function modelClass(): string
    {
        return Proyek::class;
    }

    protected function routePrefix(): string
    {
        return 'proyek';
    }

    protected function pageTitle(): string
    {
        return 'Proyek';
    }

    protected function relations(): array
    {
        return ['lokasiHutan', 'kelompokTani', 'jenisPohon'];
    }

    protected function columns(): array
    {
        return [
            ['label' => 'ID', 'key' => 'id'],
            ['label' => 'Judul', 'key' => 'judul'],
            ['label' => 'Kode Prefix', 'key' => 'kode_prefix'],
            ['label' => 'Lokasi', 'relation' => 'lokasiHutan', 'label_key' => 'nama_area_konservasi'],
            ['label' => 'Kelompok Tani', 'relation' => 'kelompokTani', 'label_key' => 'nama_kelompok'],
            ['label' => 'Jenis Pohon', 'relation' => 'jenisPohon', 'label_key' => 'nama_lokal'],
            ['label' => 'Biaya/Bibit', 'key' => 'biaya_per_bibit'],
            ['label' => 'Kuota', 'key' => 'kuota_bibit'],
            ['label' => 'Dipesan', 'key' => 'bibit_dipesan'],
            ['label' => 'Status', 'key' => 'status', 'humanize' => true],
            ['label' => 'Mulai Tanam', 'key' => 'tanggal_mulai_tanam', 'date' => true],
        ];
    }

    protected function fields(): array
    {
        return [
            [
                'name' => 'lokasi_hutan_id',
                'label' => 'Lokasi Hutan',
                'type' => 'select',
                'rules' => ['required', 'integer', 'exists:lokasi_hutan,id'],
                'options' => fn (): array => LokasiHutan::query()
                    ->orderBy('nama_area_konservasi')
                    ->pluck('nama_area_konservasi', 'id')
                    ->all(),
            ],
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
                'name' => 'jenis_pohon_id',
                'label' => 'Jenis Pohon',
                'type' => 'select',
                'rules' => ['required', 'integer', 'exists:jenis_pohon,id'],
                'options' => fn (): array => JenisPohon::query()
                    ->orderBy('nama_lokal')
                    ->pluck('nama_lokal', 'id')
                    ->all(),
            ],
            [
                'name' => 'judul',
                'label' => 'Judul',
                'type' => 'text',
                'rules' => ['required', 'string', 'max:255'],
            ],
            [
                'name' => 'kode_prefix',
                'label' => 'Kode Prefix Pohon',
                'type' => 'text',
                'placeholder' => 'Contoh: CKL-MHN',
                'help' => 'Dipakai untuk kode pohon otomatis, misalnya CKL-MHN-001.',
                'rules' => ['nullable', 'string', 'max:20', 'regex:/^[A-Z0-9-]+$/'],
            ],
            [
                'name' => 'deskripsi',
                'label' => 'Deskripsi',
                'type' => 'textarea',
                'rules' => ['required', 'string'],
                'rows' => 4,
            ],
            [
                'name' => 'target_ekologi',
                'label' => 'Target Ekologi',
                'type' => 'textarea',
                'rules' => ['required', 'string'],
                'rows' => 3,
            ],
            [
                'name' => 'biaya_per_bibit',
                'label' => 'Biaya per Bibit',
                'type' => 'number',
                'rules' => ['required', 'integer', 'min:0'],
            ],
            [
                'name' => 'kuota_bibit',
                'label' => 'Kuota Bibit',
                'type' => 'number',
                'rules' => ['required', 'integer', 'min:1'],
            ],
            [
                'name' => 'bibit_dipesan',
                'label' => 'Bibit Dipesan',
                'type' => 'number',
                'default' => 0,
                'rules' => ['nullable', 'integer', 'min:0'],
            ],
            [
                'name' => 'tanggal_mulai_tanam',
                'label' => 'Tanggal Mulai Tanam',
                'type' => 'date',
                'rules' => ['required', 'date'],
            ],
            [
                'name' => 'tanggal_selesai_tanam',
                'label' => 'Tanggal Selesai Tanam',
                'type' => 'date',
                'rules' => ['required', 'date', 'after_or_equal:tanggal_mulai_tanam'],
            ],
            [
                'name' => 'status',
                'label' => 'Status',
                'type' => 'select',
                'default' => 'draf',
                'rules' => ['required', 'string', 'in:draf,aktif,penuh,selesai,nonaktif'],
                'options' => [
                    'draf' => 'Draf',
                    'aktif' => 'Aktif',
                    'penuh' => 'Penuh',
                    'selesai' => 'Selesai',
                    'nonaktif' => 'Nonaktif',
                ],
            ],
        ];
    }
}
