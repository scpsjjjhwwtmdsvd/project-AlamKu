@extends('layouts.app')

@section('title', 'Dasbor Admin')

@section('content')
    <section class="admin-hero" data-module="proyek">
        <div>
            <h1>Kendali data konservasi.</h1>
            <p>
                Pantau seluruh modul AlamKu: program tanam, adopsi donatur,
                pembayaran, pohon, dan verifikasi laporan lapangan.
            </p>
            <div class="admin-hero-meta">
                <span class="admin-pill">{{ number_format($stats[2]['value'] ?? 0, 0, ',', '.') }} proyek</span>
                <span class="admin-pill">{{ number_format($stats[1]['value'] ?? 0, 0, ',', '.') }} mitra tani</span>
                <span class="admin-pill">Diperbarui {{ now()->translatedFormat('d M Y') }}</span>
            </div>
        </div>
        <div class="hero-side-actions">
            <a class="btn btn-light" href="{{ route('proyek.create') }}">+ Proyek</a>
            <a class="btn btn-ghost-light" href="{{ route('laporan-lapangan.index') }}">Verifikasi laporan</a>
        </div>
    </section>

    <section class="portal-stats admin-stats" aria-label="Ringkasan admin">
        @foreach ($stats as $stat)
            <div class="portal-stat">
                <span class="portal-stat-label">{{ $stat['label'] }}</span>
                <strong>
                    @if ($stat['currency'] ?? false)
                        Rp {{ number_format($stat['value'], 0, ',', '.') }}
                    @else
                        {{ number_format($stat['value'], 0, ',', '.') }}
                    @endif
                </strong>
                <small>{{ $stat['hint'] }}</small>
            </div>
        @endforeach
    </section>

    <section class="portal-section">
        <div class="portal-section-head">
            <div>
                <h2>Semua yang perlu dikelola.</h2>
            </div>
        </div>

        <div class="admin-module-grid">
            @foreach ($modules as $module)
                <a class="admin-module" href="{{ route($module['route']) }}">
                    <span class="admin-module-icon">{{ mb_strtoupper(mb_substr($module['label'], 0, 1)) }}</span>
                    <div class="admin-module-body">
                        <strong>{{ $module['label'] }}</strong>
                        <span>{{ $module['desc'] }}</span>
                    </div>
                    <span class="admin-module-count">{{ number_format($module['count'], 0, ',', '.') }}</span>
                </a>
            @endforeach
        </div>
    </section>

    <div class="portal-split">
        <section class="portal-section">
            <div class="portal-section-head">
                <div>
                    <h2>Adopsi masuk.</h2>
                </div>
                <a class="btn btn-sm" href="{{ route('pesanan-adopsi.index') }}">Kelola</a>
            </div>

            <div class="data-card">
                @if ($recentOrders->isEmpty())
                    <div class="empty-state">
                        <strong>Belum ada pesanan.</strong>
                        <span>Pesanan adopsi dari donatur akan muncul di sini.</span>
                    </div>
                @else
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Donatur</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentOrders as $order)
                                    <tr>
                                        <td data-label="Kode">{{ $order->kode_pesanan }}</td>
                                        <td data-label="Donatur">{{ $order->donatur?->name ?? '-' }}</td>
                                        <td data-label="Total">Rp {{ number_format($order->total_bayar, 0, ',', '.') }}</td>
                                        <td data-label="Status">
                                            <span class="status-badge status-{{ $order->status }}">{{ \Illuminate\Support\Str::headline($order->status) }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </section>

        <section class="portal-section">
            <div class="portal-section-head">
                <div>
                    <h2>Antrean verifikasi.</h2>
                </div>
                <a class="btn btn-sm" href="{{ route('laporan-lapangan.index') }}">Kelola</a>
            </div>

            @if ($recentReports->isEmpty())
                <div class="panel empty-state">
                    <strong>Belum ada laporan.</strong>
                    <span>Laporan dari mitra tani akan muncul di sini.</span>
                </div>
            @else
                <div class="report-list">
                    @foreach ($recentReports as $report)
                        <article class="report-item">
                            <div class="report-main">
                                <span class="status-badge status-{{ $report->status }}">{{ \Illuminate\Support\Str::headline($report->status) }}</span>
                                <h3>{{ $report->judul }}</h3>
                                <p>{{ $report->proyek?->judul ?? '-' }} &middot; {{ $report->pengguna?->name ?? 'Pelapor tidak diketahui' }}</p>
                            </div>
                            <span class="report-condition">{{ \Illuminate\Support\Str::headline($report->kondisi) }}</span>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    <section class="portal-section">
        <div class="portal-section-head">
            <div>
                <h2>Adopsi bibit terkini.</h2>
            </div>
            <a class="btn btn-dark" href="{{ route('proyek.index') }}">Semua proyek</a>
        </div>

        @if ($projects->isEmpty())
            <div class="panel empty-state">
                <strong>Belum ada proyek.</strong>
                <span>Mulai dengan membuat program konservasi pertama.</span>
                <div style="margin-top:18px">
                    <a class="btn btn-dark" href="{{ route('proyek.create') }}">Tambah proyek</a>
                </div>
            </div>
        @else
            <div class="order-list">
                @foreach ($projects as $project)
                    @php
                        $percent = (int) $project->kuota_bibit > 0
                            ? min(100, (int) round(($project->bibit_dipesan / $project->kuota_bibit) * 100))
                            : 0;
                    @endphp
                    <article class="order-card">
                        <div class="order-main">
                            <span class="status-badge status-{{ $project->status }}">{{ ucfirst(str_replace('_', ' ', $project->status)) }}</span>
                            <h3>{{ $project->judul }}</h3>
                            <p class="order-meta">
                                {{ $project->lokasiHutan?->nama_area_konservasi ?? 'Lokasi belum diisi' }}
                                &middot; {{ $project->jenisPohon?->nama_lokal ?? 'Jenis pohon belum diisi' }}
                            </p>
                            <div class="progress" style="margin-top:12px" aria-hidden="true">
                                <span @style(['width: ' . $percent . '%'])></span>
                            </div>
                        </div>
                        <div class="order-side">
                            <strong>{{ number_format($project->bibit_dipesan, 0, ',', '.') }} / {{ number_format($project->kuota_bibit, 0, ',', '.') }}</strong>
                            <span class="muted" style="font-size:10.5px">bibit diadopsi</span>
                            <a class="btn btn-sm" href="{{ route('proyek.show', $project) }}">Kelola</a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection
