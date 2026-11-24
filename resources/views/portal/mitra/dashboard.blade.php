@extends('layouts.portal', ['role' => 'mitra'])

@section('title', 'Ringkasan Tani')

@section('content')
    @php
        $firstName = \Illuminate\Support\Str::of($user->name)->explode(' ')->first();
        $projectImages = [
            'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1511497584788-876760111969?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=1000&q=80',
        ];
    @endphp

    <section class="portal-hero" data-role="mitra">
        <div class="portal-hero-copy">
            @if ($kelompok)
                <h1>{{ $kelompok->nama_kelompok }}</h1>
                <p>
                    Selamat datang, {{ $firstName }}. Kelola proyek, pohon, dan laporan
                    lapangan kelompok Anda dari satu ruang kerja.
                </p>
                <div class="hero-actions">
                    <a class="btn btn-light" href="{{ route('mitra.projects') }}">Lihat proyek</a>
                    <a class="btn btn-ghost-light" href="{{ route('mitra.reports.create') }}">Buat laporan</a>
                </div>
            @else
                <h1>Lengkapi profil tani.</h1>
                <p>
                    Selamat datang, {{ $firstName }}. Tambahkan profil kelompok tani
                    agar dapat mengelola proyek dan laporan lapangan.
                </p>
                <div class="hero-actions">
                    <a class="btn btn-light" href="{{ route('mitra.profile') }}">Lengkapi profil</a>
                </div>
            @endif
        </div>

        <div class="impact-card">
            <span class="impact-kicker">Status mitra</span>
            @if ($kelompok)
                <strong class="impact-number">{{ $stats['projects'] }}</strong>
                <span class="impact-caption">proyek konservasi dikelola</span>
                <div class="impact-split">
                    <div>
                        <strong>{{ number_format($stats['trees'], 0, ',', '.') }}</strong>
                        <span>Pohon</span>
                    </div>
                    <div>
                        <strong>{{ $kelompok->terverifikasi ? 'Terverifikasi' : 'Menunggu' }}</strong>
                        <span>Status mitra</span>
                    </div>
                </div>
            @else
                <strong class="impact-number">-</strong>
                <span class="impact-caption">profil kelompok belum lengkap</span>
                <div class="impact-split">
                    <div>
                        <strong>0</strong>
                        <span>Proyek</span>
                    </div>
                    <div>
                        <strong>Belum</strong>
                        <span>Status mitra</span>
                    </div>
                </div>
            @endif
        </div>
    </section>

    <section class="portal-stats" aria-label="Ringkasan kelompok tani">
        <div class="portal-stat">
            <span class="portal-stat-label">Proyek</span>
            <strong>{{ number_format($stats['projects'], 0, ',', '.') }}</strong>
            <small>Program dikelola</small>
        </div>
        <div class="portal-stat">
            <span class="portal-stat-label">Pohon</span>
            <strong>{{ number_format($stats['trees'], 0, ',', '.') }}</strong>
            <small>Pohon terdaftar</small>
        </div>
        <div class="portal-stat">
            <span class="portal-stat-label">Laporan</span>
            <strong>{{ number_format($stats['reports'], 0, ',', '.') }}</strong>
            <small>Laporan lapangan</small>
        </div>
        <div class="portal-stat">
            <span class="portal-stat-label">Bibit diadopsi</span>
            <strong>{{ number_format($stats['adoptions'], 0, ',', '.') }}</strong>
            <small>Dari program Anda</small>
        </div>
    </section>

    <section class="portal-section" id="notifikasi-adopsi">
        <div class="portal-section-head">
            <div>
                <span class="section-label">Notifikasi</span>
                <h2>Donasi yang sudah dibayar.</h2>
            </div>
            @if ($donationSummary['recent'] > 0)
                <span class="notif-count">{{ $donationSummary['recent'] }} baru minggu ini</span>
            @endif
        </div>

        @if ($donations->isEmpty())
            <div class="panel empty-state">
                <strong>Belum ada donasi terkonfirmasi.</strong>
                <span>Donasi yang dibayar donatur akan muncul di sini secara otomatis.</span>
            </div>
        @else
            <div class="data-card">
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Donatur</th>
                                <th>Proyek</th>
                                <th>Bibit</th>
                                <th>Nilai</th>
                                <th>Dikonfirmasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($donations as $donation)
                                <tr>
                                    <td data-label="Donatur">{{ $donation->donatur?->name ?? 'Donatur' }}</td>
                                    <td data-label="Proyek">{{ $donation->proyek?->judul ?? '-' }}</td>
                                    <td data-label="Bibit">{{ number_format($donation->jumlah_bibit, 0, ',', '.') }}</td>
                                    <td data-label="Nilai">Rp {{ number_format($donation->total_bayar, 0, ',', '.') }}</td>
                                    <td data-label="Dikonfirmasi">{{ $donation->dikonfirmasi_pada?->format('d M Y H:i') ?? '-' }} WIB</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <p class="fund-note">
                Donasi sudah dikonfirmasi otomatis pada mode demo, jadi Anda tidak perlu menunggu persetujuan admin.
            </p>
        @endif
    </section>

    <section class="portal-section">
        <div class="portal-section-head">
            <div>
                <span class="section-label">Dana</span>
                <h2>Rincian dana proyek.</h2>
            </div>
        </div>

        @if ($funds->isEmpty())
            <div class="panel empty-state">
                <strong>Belum ada proyek dengan rencana dana.</strong>
                <span>Admin akan menambahkan alokasi dana untuk setiap proyek kelompok Anda.</span>
            </div>
        @else
            <div class="data-card">
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Proyek</th>
                                <th>Dana diterima</th>
                                <th>Rencana</th>
                                <th>Realisasi</th>
                                <th>Sisa rencana</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($funds as $fund)
                                <tr>
                                    <td data-label="Proyek">
                                        <a href="{{ route('mitra.projects.show', $fund['project']) }}">{{ $fund['project']->judul }}</a>
                                    </td>
                                    <td data-label="Dana diterima">Rp {{ number_format($fund['received'], 0, ',', '.') }}</td>
                                    <td data-label="Rencana">Rp {{ number_format($fund['planned'], 0, ',', '.') }}</td>
                                    <td data-label="Realisasi">Rp {{ number_format($fund['realized'], 0, ',', '.') }}</td>
                                    <td data-label="Sisa rencana">Rp {{ number_format(max(0, $fund['planned'] - $fund['realized']), 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <p class="fund-note">
                Dana diterima dihitung dari donasi terkonfirmasi. Rencana dan realisasi berasal dari alokasi dana yang dicatat admin.
            </p>
        @endif
    </section>

    <section class="portal-section">
        <div class="portal-section-head">
            <div>
                <h2>Program yang Anda kelola.</h2>
            </div>
            <a class="btn" href="{{ route('mitra.projects') }}">Semua proyek</a>
        </div>

        @if ($projects->isEmpty())
            <div class="panel empty-state">
                <strong>Belum ada proyek terhubung.</strong>
                <span>Admin akan menghubungkan program konservasi ke kelompok tani Anda.</span>
            </div>
        @else
            <div class="project-grid">
                @foreach ($projects as $project)
                    @php
                        $percent = (int) $project->kuota_bibit > 0
                            ? min(100, (int) round(($project->bibit_dipesan / $project->kuota_bibit) * 100))
                            : 0;
                    @endphp
                    <a class="project-card" href="{{ route('mitra.projects.show', $project) }}">
                        <div class="project-media">
                            <img src="{{ $projectImages[$loop->index % count($projectImages)] }}" alt="" loading="lazy">
                        </div>
                        <div class="project-body">
                            <span class="pill">{{ ucfirst(str_replace('_', ' ', $project->status)) }}</span>
                            <h3>{{ $project->judul }}</h3>
                            <p>
                                {{ $project->lokasiHutan?->nama_area_konservasi ?? 'Lokasi belum diisi' }}
                                &middot; {{ $project->jenisPohon?->nama_lokal ?? 'Jenis pohon belum diisi' }}
                            </p>
                            <div class="progress" aria-hidden="true">
                                <span @style(['width: ' . $percent . '%'])></span>
                            </div>
                            <small>{{ number_format($project->bibit_dipesan, 0, ',', '.') }} / {{ number_format($project->kuota_bibit, 0, ',', '.') }} bibit diadopsi</small>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    <div class="portal-split">
        <section class="portal-section">
            <div class="portal-section-head">
                <div>
                    <h2>Kabar dari lapangan.</h2>
                </div>
                <a class="btn btn-sm" href="{{ route('mitra.reports') }}">Semua</a>
            </div>

            @if ($reports->isEmpty())
                <div class="panel empty-state">
                    <strong>Belum ada laporan.</strong>
                    <span>Kirim laporan pertama setelah melakukan pemantauan lapangan.</span>
                    <div style="margin-top:16px">
                        <a class="btn btn-dark" href="{{ route('mitra.reports.create') }}">Buat laporan</a>
                    </div>
                </div>
            @else
                <div class="report-list">
                    @foreach ($reports as $report)
                        <article class="report-item">
                            <div class="report-main">
                                <span class="status-badge status-{{ $report->status }}">{{ \Illuminate\Support\Str::headline($report->status) }}</span>
                                <h3>{{ $report->judul }}</h3>
                                <p>{{ $report->proyek?->judul ?? '-' }} &middot; {{ $report->tanggal_laporan?->format('d M Y') }}</p>
                            </div>
                            <span class="report-condition">{{ \Illuminate\Support\Str::headline($report->kondisi) }}</span>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="portal-section">
            <div class="portal-section-head">
                <div>
                    <h2>Bibit di program Anda.</h2>
                </div>
                <a class="btn btn-sm" href="{{ route('mitra.trees') }}">Semua</a>
            </div>

            @if ($trees->isEmpty())
                <div class="panel empty-state">
                    <strong>Belum ada pohon.</strong>
                    <span>Pohon akan muncul setelah admin mencatat penanaman.</span>
                </div>
            @else
                <div class="report-list">
                    @foreach ($trees as $tree)
                        <article class="report-item">
                            <div class="report-main">
                                <span class="tree-code">{{ $tree->kode_pohon }}</span>
                                <h3>{{ $tree->jenisPohon?->nama_lokal ?? 'Jenis pohon' }}</h3>
                                <p>{{ $tree->proyek?->judul ?? '-' }}</p>
                            </div>
                            <span class="status-badge status-{{ $tree->status_kesehatan }}">{{ \Illuminate\Support\Str::headline($tree->status_kesehatan) }}</span>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
