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
                            <th>Jenis</th>
                            <th>Status</th>
                            <th>Tanggal Tanam</th>
                            <th>Koordinat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($trees as $tree)
                            <tr>
                                <td data-label="Kode"><span class="tree-code">{{ $tree->kode_pohon }}</span></td>
                                <td data-label="Proyek">{{ $tree->proyek?->judul ?? '-' }}</td>
                                <td data-label="Jenis">{{ $tree->jenisPohon?->nama_lokal ?? '-' }}</td>
                                <td data-label="Status">
                                    <span class="status-badge status-{{ $tree->status_kesehatan }}">{{ \Illuminate\Support\Str::headline($tree->status_kesehatan) }}</span>
                                </td>
                                <td data-label="Tanggal Tanam">{{ $tree->tanggal_tanam?->format('d M Y') ?? '-' }}</td>
                                <td data-label="Koordinat">{{ $tree->latitude && $tree->longitude ? $tree->latitude.', '.$tree->longitude : '-' }}</td>
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
