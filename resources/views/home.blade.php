@extends('layouts.site')

@section('title', 'Konservasi yang bisa dilacak')

@push('head')
    <link rel="stylesheet" href="{{ asset('css/pages/home.css') }}">
@endpush

@section('content')
    @php
        $projectImages = [
            'https://images.unsplash.com/photo-1448375240586-882707db888b?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1000&q=80',
        ];
    @endphp

    <section class="hero">
        <img
            class="hero-bg"
            src="{{ asset('images/hero-mountain.jpg') }}"
            alt="Gunung dengan puncak bergerigi dan padang hijau di kaki lerengnya"
            fetchpriority="high"
        >
        <div class="hero-inner">
            <h1 class="hero-title">AlamKu</h1>
            <p class="hero-sub">
                Hutan yang lestari dimulai dari satu pohon. Adopsi bibit,
                ikuti pertumbuhannya, dan saksikan dampaknya melalui laporan lapangan
                yang transparan.
            </p>
            <div class="hero-actions">
                <a class="btn btn-light btn-lg" href="{{ route('program.index') }}">Jelajahi Program</a>
            </div>
        </div>
    </section>

    <section class="stats-strip" aria-label="Ringkasan data">
        @foreach ($stats as $stat)
            <div class="stat">
                <strong>{{ number_format($stat['value'], 0, ',', '.') }}</strong>
                <span>{{ $stat['label'] }}</span>
            </div>
        @endforeach
    </section>

    @if ($projects->isNotEmpty())
        <section class="section" id="program">
            <div class="section-head">
                <div>
                    <h2 class="display">Sedang <em>tumbuh</em> sekarang.</h2>
                </div>
                <a class="btn btn-dark" href="{{ route('program.index') }}">Lihat semua program</a>
            </div>

            <div class="project-grid">
                @foreach ($projects as $project)
                    @php
                        $percent = $project->kuota_bibit > 0
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
                                &middot;
                                {{ $project->jenisPohon?->nama_lokal ?? 'Jenis pohon belum diisi' }}
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

    <section class="section" id="ruang">
        <div class="section-head">
            <div>
                <h2 class="display">Satu ekosistem, <em>tiga peran</em>.</h2>
            </div>
            <p>
                Donatur, mitra tani, dan admin punya ruang kerja masing-masing.
                Datanya terhubung, tampilannya tetap satu bahasa desain.
            </p>
        </div>

        <div class="role-portal-grid">
            <article class="role-portal-card role-portal-donatur">
                <span class="role-chip role-chip-donatur">Ruang Donatur</span>
                <h3>Adopsi & pantau.</h3>
                <p>Pilih program, lihat pohon yang Anda dukung, dan ikuti setiap laporan pertumbuhannya.</p>
                <ul>
                    <li>Adopsi bibit dari program aktif</li>
                    <li>Riwayat pesanan & pembayaran</li>
                    <li>Kabar pohon dan laporan lapangan</li>
                </ul>
                <a class="btn" href="{{ route('program.index') }}">Jelajahi program</a>
            </article>

            <article class="role-portal-card role-portal-mitra">
                <span class="role-chip role-chip-mitra">Ruang Tani</span>
                <h3>Kelola lapangan.</h3>
                <p>Kelola profil kelompok, proyek yang diampu, pohon, dan laporan kondisi di lapangan.</p>
                <ul>
                    <li>Profil & legalitas kelompok tani</li>
                    <li>Daftar proyek dan pohon</li>
                    <li>Kirim laporan pemantauan</li>
                </ul>
                <a class="btn" href="{{ route('register', ['role' => 'mitra']) }}">Daftar mitra tani</a>
            </article>

            <article class="role-portal-card role-portal-admin">
                <span class="role-chip role-chip-admin">Ruang Admin</span>
                <h3>Verifikasi data.</h3>
                <p>Satu konsol untuk seluruh modul: program, adopsi, pembayaran, pohon, dan laporan.</p>
                <ul>
                    <li>CRUD seluruh data konservasi</li>
                    <li>Verifikasi mitra dan laporan</li>
                    <li>Pemetaan pohon ke donatur</li>
                </ul>
                <a class="btn" href="{{ route('login') }}">Masuk admin</a>
            </article>
        </div>
    </section>

    <section class="section split" id="tentang">
        <div>
            <h2 class="display">Transparansi dari <em>benih</em> hingga tumbuh.</h2>
            <p class="lead">
                Setiap pohon punya cerita, koordinat, dan penanggung jawab.
                AlamKu menghubungkan donatur, kelompok tani, dan tim lapangan
                agar setiap dampak bisa dilihat dan diverifikasi.
            </p>

            <div class="mini-stats">
                <div class="mini-stat">
                    <strong>{{ number_format($totalKuota, 0, ',', '.') }}</strong>
                    <span>Kuota bibit</span>
                </div>
                <div class="mini-stat">
                    <strong>{{ number_format($totalAdopsi, 0, ',', '.') }}</strong>
                    <span>Bibit diadopsi</span>
                </div>
                <div class="mini-stat">
                    <strong>{{ number_format($activeProjects, 0, ',', '.') }}</strong>
                    <span>Program aktif</span>
                </div>
            </div>
        </div>

        <div class="feature-stack">
            <div class="feature-panel">
                <span class="feature-icon" aria-hidden="true">
                    <svg width="21" height="21" viewBox="0 0 24 24" fill="none">
                        <path d="M10 13a5 5 0 0 0 7.1.1l2-2a5 5 0 0 0-7.1-7.1l-1.1 1.1" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                        <path d="M14 11a5 5 0 0 0-7.1-.1l-2 2A5 5 0 0 0 12 20l1.1-1.1" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                    </svg>
                </span>
                <div>
                    <h3>Data yang terhubung</h3>
                    <p>Proyek, pohon, pesanan, pembayaran, dan laporan saling terhubung tanpa entri ganda.</p>
                </div>
            </div>

            <div class="feature-panel">
                <span class="feature-icon" aria-hidden="true">
                    <svg width="21" height="21" viewBox="0 0 24 24" fill="none">
                        <path d="M12 21s7-5.4 7-11a7 7 0 1 0-14 0c0 5.6 7 11 7 11Z" stroke="currentColor" stroke-width="1.7"/>
                        <circle cx="12" cy="10" r="2.4" stroke="currentColor" stroke-width="1.7"/>
                    </svg>
                </span>
                <div>
                    <h3>Lokasi & geotag</h3>
                    <p>Koordinat pohon dan foto lapangan tersimpan rapi untuk kebutuhan audit dan verifikasi.</p>
                </div>
            </div>

            <div class="feature-panel">
                <span class="feature-icon" aria-hidden="true">
                    <svg width="21" height="21" viewBox="0 0 24 24" fill="none">
                        <path d="M12 3 4.5 6v5.2c0 4.4 3.1 8 7.5 9.8 4.4-1.8 7.5-5.4 7.5-9.8V6L12 3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                        <path d="m8.8 12 2.2 2.2 4.2-4.4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <div>
                    <h3>Dampak yang terukur</h3>
                    <p>Serapan karbon, jumlah bibit, dan status kesehatan pohon menjadi dasar laporan dampak.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <h2 class="display">Tiga langkah, satu <em>dampak</em>.</h2>

        <div class="steps-grid">
            <div class="step-card">
                <span>01</span>
                <h3>Pilih program</h3>
                <p>Lihat lokasi hutan, jenis pohon, kelompok tani, dan sisa kuota bibit yang tersedia.</p>
            </div>
            <div class="step-card">
                <span>02</span>
                <h3>Adopsi bibit</h3>
                <p>Pilih paket adopsi. Pesanan tersimpan di ruang donatur beserta rincian pembayarannya.</p>
            </div>
            <div class="step-card">
                <span>03</span>
                <h3>Pantau pertumbuhan</h3>
                <p>Setelah ditanam, pohon diberi kode dan laporan lapangan muncul di akun Anda.</p>
            </div>
        </div>
    </section>

    <section class="cta-band">
        <div>
            <h2 class="display">Tumbuhkan dampak yang bisa <em>dilacak</em>.</h2>
        </div>
        @guest
            <a class="btn btn-light btn-lg" href="{{ route('register') }}">Jadi donatur</a>
        @endguest
    </section>
@endsection
