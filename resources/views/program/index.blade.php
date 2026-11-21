@extends('layouts.site')

@section('title', 'Program Konservasi')

@section('body-class', 'page-programs')

@push('head')
    <link rel="stylesheet" href="{{ asset('css/pages/program-index.css') }}">
@endpush

@section('content')
    @php
        $projectImages = [
            'https://images.unsplash.com/photo-1448375240586-882707db888b?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1511497584788-876760111969?auto=format&fit=crop&w=1200&q=80',
        ];
    @endphp

    <section class="page-hero">
        <img
            class="page-hero-bg"
            src="https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=2200&q=80"
            alt="Hutan hijau dengan cahaya pagi"
        >
        <div class="page-hero-inner">
            <h1 class="display">Pilih pohon.<br><em>Ikuti ceritanya.</em></h1>
            <p>
                Setiap program menghubungkan satu lokasi hutan, satu kelompok tani,
                dan satu jenis pohon yang bisa Anda dukung sepanjang pertumbuhannya.
            </p>

            <form class="program-search" method="GET" action="{{ route('program.index') }}">
                <label for="search">Cari program atau lokasi</label>
                <div class="program-search-row">
                    <input id="search" type="search" name="search" value="{{ $search }}" placeholder="Contoh: Cikole, mahoni, atau restorasi">
                    <button class="btn btn-dark" type="submit">Cari program</button>
                </div>
            </form>
        </div>
    </section>

    <section class="stats-strip program-stats" aria-label="Ringkasan program">
        <div class="stat">
            <strong>{{ number_format($stats['projects'], 0, ',', '.') }}</strong>
            <span>Program aktif</span>
        </div>
        <div class="stat">
            <strong>{{ number_format($stats['kuota'], 0, ',', '.') }}</strong>
            <span>Kuota bibit</span>
        </div>
        <div class="stat">
            <strong>{{ number_format($stats['adopsi'], 0, ',', '.') }}</strong>
            <span>Bibit diadopsi</span>
        </div>
        <div class="stat">
            <strong>{{ number_format($stats['donatur'], 0, ',', '.') }}</strong>
            <span>Adopsi terkonfirmasi</span>
        </div>
    </section>

    <section class="section">
        <div class="section-head">
            <div>
                <h2 class="display">Program yang bisa <em>didukung</em>.</h2>
            </div>
            <p>
                Pilih program, tentukan jumlah bibit, lalu ikuti kabar pohon
                melalui laporan lapangan dan foto geotag.
            </p>
        </div>

        @if ($projects->isEmpty())
            <div class="panel empty-state">
                <strong>Tidak ada program yang cocok.</strong>
                <span>Coba kata kunci lain, atau kembali ke seluruh program.</span>
                <div style="margin-top:18px">
                    <a class="btn btn-dark" href="{{ route('program.index') }}">Lihat semua program</a>
                </div>
            </div>
        @else
            <div class="project-grid">
                @foreach ($projects as $project)
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
                                &middot; {{ $project->kelompokTani?->nama_kelompok ?? 'Kelompok tani belum diisi' }}
                            </p>
                            <div class="progress" aria-hidden="true">
                                <span @style(['width: ' . $percent . '%'])></span>
                            </div>
                            <small>
                                {{ number_format($project->bibit_dipesan, 0, ',', '.') }} / {{ number_format($project->kuota_bibit, 0, ',', '.') }} bibit diadopsi
                                &middot; Rp {{ number_format($project->biaya_per_bibit, 0, ',', '.') }} / bibit
                            </small>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="pagination">
                {{ $projects->links() }}
            </div>
        @endif
    </section>

    <section class="section">
        <h2 class="display">Dari adopsi ke <em>laporan</em>.</h2>

        <div class="steps-grid">
            <div class="step-card">
                <span>01</span>
                <h3>Pilih program</h3>
                <p>Lihat lokasi hutan, jenis pohon, kelompok tani, dan sisa kuota bibit yang tersedia.</p>
            </div>
            <div class="step-card">
                <span>02</span>
                <h3>Adopsi bibit</h3>
                <p>Tentukan jumlah bibit. Pesanan akan tersimpan di ruang donatur beserta batas pembayaran.</p>
            </div>
            <div class="step-card">
                <span>03</span>
                <h3>Pantau pertumbuhan</h3>
                <p>Setelah ditanam, pohon diberi kode dan laporan lapangan akan muncul di akun Anda.</p>
            </div>
        </div>
    </section>

    <section class="cta-band">
        <div>
            <h2 class="display">Satu pohon cukup untuk <em>memulai</em>.</h2>
        </div>
        @auth
            <a class="btn btn-light btn-lg" href="{{ route(\App\Support\RoleHome::routeName(auth()->user()->role)) }}">Buka ruang kerja</a>
        @else
            <a class="btn btn-light btn-lg" href="{{ route('register') }}">Daftar sebagai donatur</a>
        @endauth
    </section>
@endsection
