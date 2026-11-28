@extends('layouts.portal', ['role' => 'mitra'])

@section('title', 'Pohon Tani')

@section('content')
    <section class="portal-hero portal-hero-slim" data-role="mitra">
        <div class="portal-hero-copy">
            <h1>Pohon di program Anda.</h1>
            <p>Pantau kode pohon, jenis, status kesehatan, dan koordinat penanaman.</p>
        </div>
        <div class="hero-side-actions">
            <a class="btn btn-light" href="{{ route('mitra.reports.create') }}">Buat laporan</a>
        </div>
    </section>

    <p class="section-label">Daftar pohon</p>

    <div class="panel" style="margin-bottom:18px">
        <h2 style="font-size:20px">Apa itu penyulaman?</h2>
        <p>
            <strong>Penyulaman</strong> adalah menanam ulang bibit yang mati atau rusak
            agar jumlah pohon sesuai target. Status <em>Perlu Penyulaman</em> berarti
            lubang tanam harus diisi bibit baru. Perbarui status pohon langsung pada
            kolom Status di tabel di bawah.
        </p>
    </div>

    <div class="data-card">
        @if ($trees->isEmpty())
            <div class="empty-state">
                <strong>Belum ada pohon terdaftar.</strong>
                <span>Pohon akan muncul setelah admin mencatat penanaman pada proyek kelompok Anda.</span>
            </div>
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Proyek</th>
                            <th>Status</th>
                            <th>Tanggal Tanam</th>
                            <th>Koordinat</th>
                            <th>Perbarui Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($trees as $tree)
                            <tr>
                                <td data-label="Kode"><span class="tree-code">{{ $tree->kode_pohon }}</span></td>
                                <td data-label="Proyek">
                                    <a href="{{ route('mitra.projects.show', $tree->proyek) }}">{{ $tree->proyek?->judul ?? '-' }}</a>
                                </td>
                                <td data-label="Status">
                                    <span class="status-badge status-{{ $tree->status_kesehatan }}">{{ \Illuminate\Support\Str::headline($tree->status_kesehatan) }}</span>
                                </td>
                                <td data-label="Tanggal Tanam">{{ $tree->tanggal_tanam?->format('d M Y') ?? '-' }}</td>
                                <td data-label="Koordinat">{{ $tree->latitude && $tree->longitude ? $tree->latitude.', '.$tree->longitude : '-' }}</td>
                                <td data-label="Perbarui Status">
                                    <form class="inline-status-form" method="POST" action="{{ route('mitra.trees.status', $tree) }}">
                                        @csrf
                                        @method('PUT')
                                        <select name="status_kesehatan" aria-label="Status kesehatan {{ $tree->kode_pohon }}">
                                            @foreach (['belum_ditanam' => 'Belum Ditanam', 'tumbuh_baik' => 'Tumbuh Baik', 'perlu_penyulaman' => 'Perlu Penyulaman', 'mati' => 'Mati'] as $value => $label)
                                                <option value="{{ $value }}" @selected($tree->status_kesehatan === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-sm" type="submit">Simpan</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="pagination">
        {{ $trees->links() }}
    </div>
@endsection
