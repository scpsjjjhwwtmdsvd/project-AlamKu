<?php

namespace App\Http\Controllers;

use App\Models\PesananAdopsi;
use App\Models\Pohon;
use App\Models\Proyek;
use Illuminate\Database\Eloquent\Model;

class PohonController extends CrudController
{
    protected function modelClass(): string
    {
        return Pohon::class;
    }

    protected function routePrefix(): string
    {
        return 'pohon';
    }

    protected function pageTitle(): string
    {
        return 'Pohon';
    }

    protected function relations(): array
    {
        return ['proyek'];
    }

    /**
     * Tabel pohon hanya memiliki created_at (useCurrent), sedangkan model
     * Pohon tidak menonaktifkan UPDATED_AT. Timestamps dimatikan di sini
     * agar Eloquent tidak menyisipkan kolom updated_at yang tidak ada.
     */
    protected function beforeSave(Model $item): void
    {
        $item->timestamps = false;

        // Jenis pohon selalu mengikuti proyek, supaya tidak ada data
        // pohon yang spesiesnya berbeda dari proyeknya.
        if ($item->proyek_id) {
            $item->jenis_pohon_id = Proyek::query()
                ->whereKey($item->proyek_id)
                ->value('jenis_pohon_id');
        }
    }

    protected function columns(): array
    {
        return [
            ['label' => 'ID', 'key' => 'id'],
            ['label' => 'Kode Pohon', 'key' => 'kode_pohon'],
            ['label' => 'Donatur', 'value' => fn (Pohon $pohon): string => $this->userName($pohon->donatur_id)],
            ['label' => 'Proyek', 'relation' => 'proyek', 'label_key' => 'judul'],
            ['label' => 'Status Kesehatan', 'key' => 'status_kesehatan', 'humanize' => true],
            ['label' => 'Tanggal Tanam', 'key' => 'tanggal_tanam', 'date' => true],
            ['label' => 'Latitude', 'key' => 'latitude'],
            ['label' => 'Longitude', 'key' => 'longitude'],
        ];
    }

    protected function fields(): array
    {
        return [
            [
                'name' => 'kode_pohon',
                'label' => 'Kode Pohon',
                'type' => 'text',
                'unique' => true,
                'rules' => ['required', 'string', 'max:255'],
            ],
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
                'name' => 'donatur_id',
                'label' => 'Donatur',
                'type' => 'search-select',
                'rules' => ['required', 'integer', 'exists:pengguna,id'],
                'placeholder' => 'Ketik nama donatur...',
                'options' => fn (): array => $this->userOptions(),
            ],
            [
                'name' => 'proyek_id',
                'label' => 'Proyek',
                'type' => 'select',
                'help' => 'Jenis pohon otomatis mengikuti proyek yang dipilih.',
                'rules' => ['required', 'integer', 'exists:proyek,id'],
                'options' => fn (): array => Proyek::query()
                    ->with('jenisPohon')
                    ->orderBy('judul')
                    ->get()
                    ->mapWithKeys(fn (Proyek $proyek): array => [
                        $proyek->id => $proyek->judul.' — '.($proyek->jenisPohon?->nama_lokal ?? '-'),
                    ])
                    ->all(),
            ],
            [
                'name' => 'status_kesehatan',
                'label' => 'Status Kesehatan',
                'type' => 'select',
                'default' => 'belum_ditanam',
                'rules' => ['required', 'string', 'in:belum_ditanam,tumbuh_baik,perlu_penyulaman,mati'],
                'options' => [
                    'belum_ditanam' => 'Belum Ditanam',
                    'tumbuh_baik' => 'Tumbuh Baik',
                    'perlu_penyulaman' => 'Perlu Penyulaman',
                    'mati' => 'Mati',
                ],
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
                'name' => 'tanggal_tanam',
                'label' => 'Tanggal Tanam',
                'type' => 'date',
                'nullable' => true,
                'rules' => ['nullable', 'date'],
            ],
        ];
    }
}
