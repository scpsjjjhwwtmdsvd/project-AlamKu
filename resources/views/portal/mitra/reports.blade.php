@extends('layouts.portal', ['role' => 'mitra'])

@section('title', 'Laporan Tani')

@section('content')
    <section class="portal-hero portal-hero-slim" data-role="mitra">
        <div class="portal-hero-copy">
            <h1>Kabar dari lapangan.</h1>
            <p>Dokumentasikan pertumbuhan, kondisi, dan catatan penting dari setiap pohon.</p>
        </div>
        <div class="hero-side-actions">
            <a class="btn btn-light" href="{{ route('mitra.reports.create') }}">+ Buat laporan</a>
        </div>
    </section>

    <p class="section-label">Daftar laporan</p>

    <div class="data-card">
        @if ($reports->isEmpty())
            <div class="empty-state">
                <strong>Belum ada laporan.</strong>
                <span>Laporan yang Anda kirim akan masuk ke antrean verifikasi admin.</span>
                <div style="margin-top:18px">
                    <a class="btn btn-dark" href="{{ route('mitra.reports.create') }}">Buat laporan pertama</a>
                </div>
            </div>
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Proyek</th>
                            <th>Pohon</th>
                            <th>Kondisi</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reports as $report)
                            <tr>
                                <td data-label="Judul">{{ $report->judul }}</td>
                                <td data-label="Proyek">{{ $report->proyek?->judul ?? '-' }}</td>
                                <td data-label="Pohon">{{ $report->pohon?->kode_pohon ?? '-' }}</td>
                                <td data-label="Kondisi">{{ \Illuminate\Support\Str::headline($report->kondisi) }}</td>
                                <td data-label="Tanggal">{{ $report->tanggal_laporan?->format('d M Y') ?? '-' }}</td>
                                <td data-label="Status">
                                    <span class="status-badge status-{{ $report->status }}">{{ \Illuminate\Support\Str::headline($report->status) }}</span>
                                </td>
                                <td data-label="Aksi">
                                    @if ($report->status === 'draft')
                                        <a class="btn btn-sm" href="{{ route('mitra.reports.edit', $report) }}">Ubah kondisi</a>
                                    @else
                                        <span class="muted">Terverifikasi</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="pagination">
        {{ $reports->links() }}
    </div>
@endsection
