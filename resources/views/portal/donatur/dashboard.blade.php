@extends('layouts.portal', ['role' => 'donatur'])

@section('title', 'Ringkasan Donatur')

@section('content')
    @php
        $firstName = \Illuminate\Support\Str::of($user->name)->explode(' ')->first();
        $projectImages = [
            'https://images.unsplash.com/photo-1448375240586-882707db888b?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1000&q=80',
        ];
    @endphp

    <section class="portal-hero" data-role="donatur">
        <div class="portal-hero-copy">
            <h1>Halo, {{ $firstName }}.</h1>
            <p>
                Terima kasih sudah menumbuhkan hutan bersama AlamKu. Di sini Anda
                bisa memantau adopsi, pohon yang Anda dukung, dan laporan dari lapangan.
            </p>
            <div class="hero-actions">
                <a class="btn btn-light" href="{{ route('program.index') }}">Jelajahi program</a>
                <a class="btn btn-ghost-light" href="{{ route('akun.adoptions') }}">Lihat adopsi saya</a>
            </div>
        </div>

        <div class="impact-card">
            <span class="impact-kicker">Dampak Anda</span>
            <strong class="impact-number">{{ number_format($stats['trees'], 0, ',', '.') }}</strong>
            <span class="impact-caption">pohon terhubung dengan akun ini</span>
            <div class="impact-split">
                <div>
                    <strong>Rp {{ number_format($stats['contribution'], 0, ',', '.') }}</strong>
                    <span>Total kontribusi</span>
                </div>
                <div>
                    <strong>{{ number_format($stats['active'], 0, ',', '.') }}</strong>
                    <span>Adopsi aktif</span>
                </div>
            </div>
        </div>
    </section>

    <section class="portal-stats" aria-label="Ringkasan donatur">
        <div class="portal-stat">
            <span class="portal-stat-label">Adopsi</span>
            <strong>{{ number_format($stats['adoptions'], 0, ',', '.') }}</strong>
            <small>Pesanan adopsi</small>
        </div>
        <div class="portal-stat">
            <span class="portal-stat-label">Pohon</span>
            <strong>{{ number_format($stats['trees'], 0, ',', '.') }}</strong>
            <small>Pohon terdaftar</small>
        </div>
        <div class="portal-stat">
            <span class="portal-stat-label">Kontribusi</span>
            <strong>Rp {{ number_format($stats['contribution'], 0, ',', '.') }}</strong>
            <small>Total nilai adopsi</small>
        </div>
        <div class="portal-stat">
            <span class="portal-stat-label">Aktif</span>
            <strong>{{ number_format($stats['active'], 0, ',', '.') }}</strong>
            <small>Menunggu / terkonfirmasi</small>
        </div>
    </section>

    <section class="portal-section">
        <div class="portal-section-head">
            <div>
                <h2>Jejak kontribusi Anda.</h2>
            </div>
            <a class="btn" href="{{ route('akun.adoptions') }}">Semua adopsi</a>
        </div>

        @if ($latestOrders->isEmpty())
            <div class="panel empty-state">
                <strong>Belum ada adopsi.</strong>
                <span>Pilih program yang sedang tumbuh untuk mulai mengadopsi pohon pertama Anda.</span>
                <div style="margin-top:18px">
                    <a class="btn btn-dark" href="{{ route('program.index') }}">Jelajahi program</a>
                </div>
            </div>
        @else
            <div class="order-list">
                @foreach ($latestOrders as $order)
                    <article class="order-card">
                        <div class="order-main">
                            <span class="status-badge status-{{ $order->status }}">{{ \Illuminate\Support\Str::headline($order->status) }}</span>
                            <h3>{{ $order->proyek?->judul ?? 'Program konservasi' }}</h3>
                            <p class="order-meta">
                                {{ $order->kode_pesanan }}
                                &middot; {{ number_format($order->jumlah_bibit, 0, ',', '.') }} bibit
                                &middot; {{ $order->proyek?->lokasiHutan?->nama_area_konservasi ?? 'Lokasi belum diisi' }}
                            </p>
                        </div>
                        <div class="order-side">
                            <strong>Rp {{ number_format($order->total_bayar, 0, ',', '.') }}</strong>
                            <a class="btn btn-sm" href="{{ route('akun.adoptions.show', $order->id) }}">Lihat detail</a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section class="portal-section">
        <div class="portal-section-head">
            <div>
                <h2>Yang sedang tumbuh.</h2>
            </div>
        </div>

        @if ($trees->isEmpty())
            <div class="panel empty-state">
                <strong>Belum ada pohon yang terhubung.</strong>
                <span>Pohon akan muncul setelah admin memetakan adopsi Anda ke bibit di lapangan.</span>
            </div>
        @else
            <div class="tree-grid">
                @foreach ($trees as $tree)
                    <article class="tree-card">
                        <div class="tree-card-head">
                            <span class="tree-code">{{ $tree->kode_pohon }}</span>
                            <span class="status-badge status-{{ $tree->status_kesehatan }}">{{ \Illuminate\Support\Str::headline($tree->status_kesehatan) }}</span>
                        </div>
                        <h3>{{ $tree->jenisPohon?->nama_lokal ?? 'Jenis pohon' }}</h3>
                        <p>{{ $tree->proyek?->judul ?? 'Program konservasi' }}</p>
                        <dl class="tree-facts">
                            <div><dt>Tanggal tanam</dt><dd>{{ $tree->tanggal_tanam?->format('d M Y') ?? '-' }}</dd></div>
                            <div><dt>Koordinat</dt><dd>{{ $tree->latitude && $tree->longitude ? $tree->latitude.', '.$tree->longitude : '-' }}</dd></div>
                        </dl>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    @if ($recommended->isNotEmpty())
        <section class="portal-section">
            <div class="portal-section-head">
                <div>
                    <h2>Program yang sedang tumbuh.</h2>
                </div>
                <a class="btn btn-dark" href="{{ route('program.index') }}">Lihat semua</a>
            </div>

            <div class="project-grid">
                @foreach ($recommended as $project)
                    @php
                        $percent = (int) $project->kuota_bibit > 0
                            ? min(100, (int) round(($project->bibit_dipesan / $project->kuota_bibit) * 100))
                            : 0;
                    @endphp
                    <a class="project-card" href="{{ route('program.show', $project) }}">
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
        </section>
    @endif
@endsection
