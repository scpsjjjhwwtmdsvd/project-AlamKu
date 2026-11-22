@extends('layouts.site')

@section('title', $proyek->judul)

@section('body-class', 'page-program-detail')

@push('head')
    <link rel="stylesheet" href="{{ asset('css/pages/program-show.css') }}">
@endpush

@section('content')
    @php
        $projectImages = [
            'https://images.unsplash.com/photo-1448375240586-882707db888b?auto=format&fit=crop&w=2200&q=80',
            'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?auto=format&fit=crop&w=2200&q=80',
            'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=2200&q=80',
            'https://images.unsplash.com/photo-1511497584788-876760111969?auto=format&fit=crop&w=2200&q=80',
        ];
        $image = $projectImages[$proyek->id % count($projectImages)];
    @endphp

    <section class="program-detail-hero">
        <img class="program-detail-bg" src="{{ $image }}" alt="" fetchpriority="high">
        <div class="program-detail-inner">
            <a class="back-link" href="{{ route('program.index') }}">&larr; Semua program</a>
            <span class="pill">{{ ucfirst(str_replace('_', ' ', $proyek->status)) }}</span>
            <h1>{{ $proyek->judul }}</h1>
            <p>{{ $proyek->deskripsi }}</p>
            <div class="program-meta-row">
                <span><strong>Lokasi</strong>{{ $proyek->lokasiHutan?->nama_area_konservasi ?? '-' }}</span>
                <span><strong>Kelompok</strong>{{ $proyek->kelompokTani?->nama_kelompok ?? '-' }}</span>
                <span><strong>Jenis</strong>{{ $proyek->jenisPohon?->nama_lokal ?? '-' }}</span>
                <span><strong>Tanam</strong>{{ $proyek->tanggal_mulai_tanam?->format('M Y') ?? '-' }}</span>
            </div>
        </div>
    </section>

    <section class="program-detail-body">
        <div class="program-detail-grid">
            <div class="program-detail-main">
                <div class="panel">
                    <h2>{{ $proyek->judul }}</h2>
                    <p>{{ $proyek->deskripsi }}</p>
                </div>

                <div class="panel">
                    <h2>Dampak yang ingin dicapai.</h2>
                    <p>{{ $proyek->target_ekologi }}</p>
                </div>

                <div class="program-facts">
                    <div>
                        <span>Kuota bibit</span>
                        <strong>{{ number_format($proyek->kuota_bibit, 0, ',', '.') }}</strong>
                    </div>
                    <div>
                        <span>Sudah diadopsi</span>
                        <strong>{{ number_format($proyek->bibit_dipesan, 0, ',', '.') }}</strong>
                    </div>
                    <div>
                        <span>Pohon terdaftar</span>
                        <strong>{{ number_format($trees, 0, ',', '.') }}</strong>
                    </div>
                    <div>
                        <span>Laporan lapangan</span>
                        <strong>{{ number_format($reports, 0, ',', '.') }}</strong>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-head">
                        <div>
                            <h2>Di mana pohon ini tumbuh.</h2>
                        </div>
                    </div>
                    <dl class="detail-list">
                        <div><dt>Area konservasi</dt><dd>{{ $proyek->lokasiHutan?->nama_area_konservasi ?? '-' }}</dd></div>
                        <div><dt>Provinsi</dt><dd>{{ $proyek->lokasiHutan?->provinsi ?? '-' }}</dd></div>
                        <div><dt>Kabupaten</dt><dd>{{ $proyek->lokasiHutan?->kabupaten ?? '-' }}</dd></div>
                        <div><dt>Luas area</dt><dd>{{ $proyek->lokasiHutan?->luas_hektar ? $proyek->lokasiHutan->luas_hektar.' ha' : '-' }}</dd></div>
                        <div><dt>Kelompok tani</dt><dd>{{ $proyek->kelompokTani?->nama_kelompok ?? '-' }}</dd></div>
                        <div><dt>Jenis pohon</dt><dd>{{ $proyek->jenisPohon?->nama_lokal ?? '-' }}</dd></div>
                    </dl>
                </div>
            </div>

            <aside class="adopt-card">
                <div class="adopt-price">
                    Rp {{ number_format(\App\Support\AdoptionPackage::MINIMUM_TRANSACTION, 0, ',', '.') }}
                    <span>minimum transaksi</span>
                </div>
                <p class="adopt-price-note">Harga paket, bukan harga satu bibit.</p>

                <div class="adopt-progress">
                    <div class="progress" aria-hidden="true">
                        <span @style(['width: ' . $percent . '%'])></span>
                    </div>
                    <div class="adopt-progress-meta">
                        <span>{{ number_format($proyek->bibit_dipesan, 0, ',', '.') }} diadopsi</span>
                        <span>{{ number_format($remaining, 0, ',', '.') }} tersisa</span>
                    </div>
                </div>

                @if ($myOrder)
                    <div class="adopt-order-note">
                        <span>Adopsi terakhir Anda</span>
                        <strong>{{ $myOrder->kode_pesanan }}</strong>
                        <a class="btn btn-sm" href="{{ route('akun.adoptions.show', $myOrder->id) }}">Lihat pesanan</a>
                    </div>
                @endif

                @auth
                    @if (auth()->user()->role === 'donatur')
                        @if ($canAdopt)
                            <form
                                class="adopt-form"
                                method="POST"
                                action="{{ route('program.adopt', $proyek) }}"
                                data-adopt-form
                            >
                                @csrf

                                <fieldset class="package-group">
                                    <legend>Pilih paket adopsi</legend>
                                    <div class="package-list">
                                        @foreach ($packages as $package)
                                            <label class="package-option {{ $package['available'] ? '' : 'is-unavailable' }}">
                                                <input
                                                    type="radio"
                                                    name="paket"
                                                    value="{{ $package['key'] }}"
                                                    required
                                                    @checked($package['key'] === $selectedPackage)
                                                    @disabled(! $package['available'])
                                                >
                                                <span class="package-card">
                                                    <span class="package-copy">
                                                        <strong>{{ $package['name'] }}</strong>
                                                        <small>{{ number_format($package['trees'], 0, ',', '.') }} bibit</small>
                                                        @unless ($package['available'])
                                                            <small class="package-unavailable">Kuota tersisa tidak cukup</small>
                                                        @endunless
                                                    </span>
                                                    <span class="package-price">Rp {{ number_format($package['price'], 0, ',', '.') }}</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </fieldset>

                                @error('paket')
                                    <div class="error">{{ $message }}</div>
                                @enderror

                                <div class="adopt-total">
                                    <span>Total bayar</span>
                                    <strong data-adopt-total>Rp {{ number_format($packages[$selectedPackage]['price'], 0, ',', '.') }}</strong>
                                </div>

                                <button class="btn btn-dark btn-lg adopt-submit" type="submit" data-adopt-submit>
                                    <span data-adopt-submit-label>Bayar Rp {{ number_format($packages[$selectedPackage]['price'], 0, ',', '.') }}</span>
                                </button>

                                <div class="pay-overlay" data-pay-overlay hidden role="status" aria-live="polite" tabindex="-1">
                                    <div class="pay-overlay-card">
                                        <span class="pay-spinner" aria-hidden="true"></span>
                                        <strong>Memproses pembayaran</strong>
                                        <span data-pay-summary>{{ $packages[$selectedPackage]['name'] }} &middot; {{ $packages[$selectedPackage]['trees'] }} bibit &middot; Rp {{ number_format($packages[$selectedPackage]['price'], 0, ',', '.') }}</span>
                                        <small>Jangan tutup halaman ini.</small>
                                    </div>
                                </div>
                            </form>
                        @else
                            <div class="adopt-closed">
                                <strong>Adopsi ditutup.</strong>
                                <span>Program ini sudah penuh atau sisa kuotanya tidak cukup untuk satu paket.</span>
                                <a class="btn" href="{{ route('program.index') }}">Lihat program lain</a>
                            </div>
                        @endif
                    @else
                        <div class="adopt-closed">
                            <strong>Khusus donatur.</strong>
                            <span>Anda masuk sebagai {{ auth()->user()->role }}. Adopsi pohon dilakukan melalui akun donatur.</span>
                            <a class="btn" href="{{ route(\App\Support\RoleHome::routeName(auth()->user()->role)) }}">Buka ruang kerja</a>
                        </div>
                    @endif
                @else
                    <div class="adopt-closed">
                        <strong>Masuk untuk mengadopsi.</strong>
                        <span>Buat akun donatur atau masuk untuk melanjutkan adopsi pohon pada program ini.</span>
                        <div class="adopt-auth-actions">
                            <a class="btn btn-dark" href="{{ route('login') }}">Masuk</a>
                            <a class="btn" href="{{ route('register') }}">Daftar</a>
                        </div>
                    </div>
                @endauth

                <details class="fund-disclosure">
                    <summary>
                        <span>Transparansi dana</span>
                        <span class="fund-disclosure-hint">Lihat rincian</span>
                    </summary>
                    <div class="fund-disclosure-body">
                        <p>Angka mengikuti paket yang dipilih.</p>
                        <ul class="fund-list" data-fund-breakdown>
                            @foreach (\App\Support\AdoptionPackage::allocation($packages[$selectedPackage]['price']) as $item)
                                <li data-fund-row data-percent="{{ $item['percent'] }}">
                                    <span class="fund-label">{{ $item['label'] }}</span>
                                    <span class="fund-percent">{{ $item['percent'] }}%</span>
                                    <strong class="fund-amount" data-fund-amount>Rp {{ number_format($item['amount'], 0, ',', '.') }}</strong>
                                </li>
                            @endforeach
                        </ul>
                        <div class="fund-foot">
                            <span>Total</span>
                            <strong><span data-fund-total>Rp {{ number_format($packages[$selectedPackage]['price'], 0, ',', '.') }}</span> &middot; 100%</strong>
                        </div>
                    </div>
                </details>

                <div class="adopt-note">
                    Mode demo: pembayaran disimulasikan dan langsung dikonfirmasi.
                    Setelah itu bibit dipetakan ke kode pohon dan laporan lapangan.
                </div>

                <script type="application/json" data-adopt-data>@json($adoptData)</script>
            </aside>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="section">
            <div class="section-head">
                <div>
                    <h2 class="display">Tetap <em>tumbuh</em> bersama.</h2>
                </div>
                <a class="btn btn-dark" href="{{ route('program.index') }}">Semua program</a>
            </div>

            <div class="project-grid">
                @foreach ($related as $project)
                    @php
                        $relatedPercent = (int) $project->kuota_bibit > 0
                            ? min(100, (int) round(($project->bibit_dipesan / $project->kuota_bibit) * 100))
                            : 0;
                    @endphp
                    <a class="project-card" href="{{ route('program.show', $project) }}">
                        <div class="project-media">
                            <img src="{{ $projectImages[$project->id % count($projectImages)] }}" alt="" loading="lazy">
                        </div>
                        <div class="project-body">
                            <span class="pill">{{ ucfirst(str_replace('_', ' ', $project->status)) }}</span>
                            <h3>{{ $project->judul }}</h3>
                            <p>
                                {{ $project->lokasiHutan?->nama_area_konservasi ?? 'Lokasi belum diisi' }}
                                &middot; {{ $project->jenisPohon?->nama_lokal ?? 'Jenis pohon belum diisi' }}
                            </p>
                            <div class="progress" aria-hidden="true">
                                <span @style(['width: ' . $relatedPercent . '%'])></span>
                            </div>
                            <small>Paket mulai Rp {{ number_format(\App\Support\AdoptionPackage::MINIMUM_TRANSACTION, 0, ',', '.') }}</small>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
@endsection

@push('scripts')
    <script src="{{ asset('js/adoption.js') }}" defer></script>
@endpush
