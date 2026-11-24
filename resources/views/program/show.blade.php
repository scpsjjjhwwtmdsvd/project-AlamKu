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
                        <div><dt>Alamat</dt><dd>{{ collect([$proyek->kelompokTani?->desa, $proyek->lokasiHutan?->kabupaten, $proyek->lokasiHutan?->provinsi])->filter()->implode(', ') ?: '-' }}</dd></div>
                        <div><dt>Provinsi</dt><dd>{{ $proyek->lokasiHutan?->provinsi ?? '-' }}</dd></div>
                        <div><dt>Kabupaten</dt><dd>{{ $proyek->lokasiHutan?->kabupaten ?? '-' }}</dd></div>
                        <div><dt>Desa</dt><dd>{{ $proyek->kelompokTani?->desa ?? '-' }}</dd></div>
                        <div><dt>Koordinat</dt><dd>
                            @if ($proyek->lokasiHutan?->latitude && $proyek->lokasiHutan?->longitude)
                                <a href="https://www.google.com/maps?q={{ $proyek->lokasiHutan->latitude }},{{ $proyek->lokasiHutan->longitude }}" target="_blank" rel="noopener">
                                    {{ $proyek->lokasiHutan->latitude }}, {{ $proyek->lokasiHutan->longitude }}
                                </a>
                            @else
                                -
                            @endif
                        </dd></div>
                        <div><dt>Luas area</dt><dd>{{ $proyek->lokasiHutan?->luas_hektar ? $proyek->lokasiHutan->luas_hektar.' ha' : '-' }}</dd></div>
                        <div><dt>Patokan lokasi</dt><dd>{{ $proyek->lokasiHutan?->deskripsi ?? '-' }}</dd></div>
                        <div><dt>Kelompok tani</dt><dd>{{ $proyek->kelompokTani?->nama_kelompok ?? '-' }}</dd></div>
                        <div><dt>Jenis pohon</dt><dd>{{ $proyek->jenisPohon?->nama_lokal ?? '-' }}</dd></div>
                    </dl>
                </div>

                <div class="panel care-panel">
                    <div class="panel-head">
                        <div>
                            <h2>Catatan risiko cuaca.</h2>
                        </div>
                    </div>
                    <p>
                        Bibit yang gagal tumbuh karena cuaca ekstrem atau bencana alam
                        tidak dapat direfund. Penggantian hanya diberikan jika terbukti
                        ada kelalaian perawatan oleh petani mitra.
                    </p>
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

                                <button class="btn btn-dark btn-lg adopt-submit" type="submit" data-adopt-submit data-checkout-open>
                                    <span data-adopt-submit-label>Bayar Rp {{ number_format($packages[$selectedPackage]['price'], 0, ',', '.') }}</span>
                                </button>

                                <dialog
                                    class="checkout-dialog"
                                    data-checkout-dialog
                                    aria-labelledby="checkout-title"
                                    tabindex="-1"
                                    @if ($errors->hasAny(['metode', 'bank', 'nomor_rekening', 'dompet', 'nomor_hp']))
                                        data-checkout-open-on-load
                                    @endif
                                >
                                    <div class="checkout-card">
                                        <div class="checkout-head">
                                            <h2 id="checkout-title">Konfirmasi pembayaran</h2>
                                            <button class="checkout-close" type="button" data-checkout-close aria-label="Tutup konfirmasi pembayaran">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                </svg>
                                            </button>
                                        </div>

                                        <div class="checkout-summary">
                                            <div class="checkout-summary-program">
                                                <span class="checkout-label">Program</span>
                                                <strong>{{ $proyek->judul }}</strong>
                                                <span class="checkout-order-meta">
                                                    Paket <b data-checkout-package>{{ $packages[$selectedPackage]['name'] }}</b>
                                                    <span aria-hidden="true">&middot;</span>
                                                    <b data-checkout-trees>{{ number_format($packages[$selectedPackage]['trees'], 0, ',', '.') }} bibit</b>
                                                </span>
                                            </div>
                                            <div class="checkout-total">
                                                <span class="checkout-label">Total pembayaran</span>
                                                <strong data-checkout-total>Rp {{ number_format($packages[$selectedPackage]['price'], 0, ',', '.') }}</strong>
                                            </div>
                                        </div>

                                        <fieldset class="pay-method-group">
                                            <legend>Metode pembayaran</legend>
                                            <div class="pay-method-list">
                                                @foreach (\App\Support\PaymentMethod::channels() as $value => $channel)
                                                    <label class="pay-method">
                                                        <input
                                                            type="radio"
                                                            name="metode"
                                                            value="{{ $value }}"
                                                            @checked(old('metode', 'qris') === $value)
                                                            required
                                                        >
                                                        <span class="pay-method-card">
                                                            <strong>{{ $channel['label'] }}</strong>
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            @error('metode')
                                                <div class="error">{{ $message }}</div>
                                            @enderror
                                        </fieldset>

                                        <div class="pay-detail pay-detail-qris" data-pay-detail="qris">
                                            <div class="qris-panel">
                                                <div class="qris-frame">
                                                    @include('partials.qris-demo')
                                                </div>
                                                <div class="qris-copy">
                                                    <strong>Pindai QRIS untuk membayar</strong>
                                                    <p>Buka aplikasi bank atau e-wallet, pilih QRIS, lalu pindai kode di samping.</p>
                                                    <span class="qris-badge">QRIS Simulasi &middot; Mode demo</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="pay-detail" data-pay-detail="virtual_account" hidden>
                                            <div class="pay-fields">
                                                <label>
                                                    <span>Bank</span>
                                                    <select name="bank" data-pay-field disabled required>
                                                        @foreach (\App\Support\PaymentMethod::banks() as $bank)
                                                            <option value="{{ $bank }}" @selected(old('bank') === $bank)>{{ $bank }}</option>
                                                        @endforeach
                                                    </select>
                                                </label>
                                                <label>
                                                    <span>Nomor rekening</span>
                                                    <input
                                                        type="text"
                                                        name="nomor_rekening"
                                                        value="{{ old('nomor_rekening') }}"
                                                        inputmode="numeric"
                                                        autocomplete="off"
                                                        placeholder="Contoh: 1234567890"
                                                        minlength="6"
                                                        maxlength="24"
                                                        pattern="[0-9]{6,24}"
                                                        title="Nomor rekening berisi 6 sampai 24 angka."
                                                        data-pay-field
                                                        disabled
                                                        required
                                                    >
                                                </label>
                                            </div>
                                            @error('bank')
                                                <div class="error">{{ $message }}</div>
                                            @enderror
                                            @error('nomor_rekening')
                                                <div class="error">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="pay-detail" data-pay-detail="dompet_digital" hidden>
                                            <div class="pay-fields">
                                                <label>
                                                    <span>E-Wallet</span>
                                                    <select name="dompet" data-pay-field disabled required>
                                                        @foreach (\App\Support\PaymentMethod::wallets() as $wallet)
                                                            <option value="{{ $wallet }}" @selected(old('dompet') === $wallet)>{{ $wallet }}</option>
                                                        @endforeach
                                                    </select>
                                                </label>
                                                <label>
                                                    <span>Nomor HP</span>
                                                    <input
                                                        type="tel"
                                                        name="nomor_hp"
                                                        value="{{ old('nomor_hp') }}"
                                                        inputmode="numeric"
                                                        autocomplete="off"
                                                        placeholder="Contoh: 081234567890"
                                                        minlength="9"
                                                        maxlength="15"
                                                        pattern="0[0-9]{8,14}"
                                                        title="Nomor HP diawali 0 dan berisi 9 sampai 15 angka."
                                                        data-pay-field
                                                        disabled
                                                        required
                                                    >
                                                </label>
                                            </div>
                                            @error('dompet')
                                                <div class="error">{{ $message }}</div>
                                            @enderror
                                            @error('nomor_hp')
                                                <div class="error">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="checkout-actions">
                                            <button class="btn" type="button" data-checkout-close>Batal</button>
                                            <button class="btn btn-dark" type="submit" data-checkout-confirm>
                                                <span data-checkout-submit-label>Konfirmasi &amp; bayar Rp {{ number_format($packages[$selectedPackage]['price'], 0, ',', '.') }}</span>
                                            </button>
                                        </div>

                                        <p class="checkout-note">
                                            Pembayaran penuh, tanpa DP. Mode demo: pembayaran disimulasikan, tanpa dana nyata.
                                        </p>
                                    </div>
                                </dialog>

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
                    Pembayaran bersifat penuh (bukan DP). Setelah itu bibit dipetakan ke kode pohon dan laporan lapangan.
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
