@extends('layouts.portal', ['role' => 'donatur'])

@section('title', 'Detail Adopsi')

@section('content')
    @php
        $project = $order->proyek;
        $paymentSuccess = session('payment_success');
        $package = \App\Support\AdoptionPackage::match((int) $order->jumlah_bibit, (int) $order->total_bayar);
        $allocation = \App\Support\AdoptionPackage::allocation((int) $order->total_bayar);
        $payment = $order->pembayaran->sortByDesc('id')->first();
        $paymentMeta = $payment?->data_respons_gateway ?? [];
        $paymentMethod = $paymentMeta['channel_label'] ?? \App\Support\PaymentMethod::label($payment?->metode);
        $paymentSource = $paymentMeta['sumber'] ?? $payment?->penyedia;
    @endphp

    @if ($paymentSuccess)
        <div class="alert success pay-flash" role="status">
            <strong>Pembayaran berhasil.</strong>
            Pesanan {{ $paymentSuccess['kode_pesanan'] }} sudah dikonfirmasi otomatis. Rincian ada di bawah.
        </div>
    @endif

    <section class="portal-hero portal-hero-slim" data-role="donatur">
        <div class="portal-hero-copy">
            <h1>{{ $order->kode_pesanan }}</h1>
            <p>
                Adopsi untuk {{ $project?->judul ?? 'program konservasi' }}.
                Status saat ini: <strong>{{ \Illuminate\Support\Str::headline($order->status) }}</strong>.
            </p>
            <div class="admin-hero-meta">
                <span class="admin-pill">{{ number_format($order->jumlah_bibit, 0, ',', '.') }} bibit</span>
                <span class="admin-pill">Rp {{ number_format($order->total_bayar, 0, ',', '.') }}</span>
            </div>
        </div>
        <div class="hero-side-actions">
            <a class="btn btn-ghost-light" href="{{ route('akun.adoptions') }}">&larr; Kembali</a>
        </div>
    </section>

    @if ($paymentSuccess)
        <section class="pay-success" role="status" aria-labelledby="pay-success-title">
            <span class="pay-success-mark" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <path d="m5 12.5 4.5 4.5L19 6.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <div class="pay-success-copy">
                <h2 id="pay-success-title">Pembayaran berhasil.</h2>
                <p>
                    Paket {{ $paymentSuccess['package'] }} untuk {{ $project?->judul ?? 'program konservasi' }}
                    sudah dikonfirmasi otomatis pada mode demo.
                </p>
                <div class="pay-success-meta">
                    <div><span>Kode pesanan</span><strong>{{ $paymentSuccess['kode_pesanan'] }}</strong></div>
                    <div><span>Bibit</span><strong>{{ number_format($paymentSuccess['trees'], 0, ',', '.') }} bibit</strong></div>
                    <div><span>Dibayar</span><strong>Rp {{ number_format($paymentSuccess['total'], 0, ',', '.') }}</strong></div>
                    <div><span>Metode</span><strong>{{ $paymentSuccess['payment_method'] ?? 'QRIS' }}</strong></div>
                    @if ($paymentSuccess['payment_source'] ?? null)
                        <div><span>Sumber</span><strong>{{ $paymentSuccess['payment_source'] }}</strong></div>
                    @endif
                    <div><span>Status</span><strong>Terkonfirmasi</strong></div>
                </div>
            </div>
            <div class="pay-success-fund">
                <div class="pay-success-fund-head">
                    <h3>Transparansi dana</h3>
                    <strong>Rp {{ number_format($paymentSuccess['total'], 0, ',', '.') }}</strong>
                </div>
                @include('portal.donatur.partials.fund-allocation', [
                    'allocation' => $paymentSuccess['allocation'],
                    'total' => $paymentSuccess['total'],
                ])
                <p class="fund-note">
                    Mode demo: pembayaran disimulasikan, tidak ada dana nyata yang ditarik.
                    Alokasi di atas adalah rencana penggunaan dana untuk transaksi ini.
                </p>
            </div>
        </section>
    @endif

    <div class="detail-layout">
        <div class="detail-stack">
            <div class="data-card">
                <table class="detail-table">
                    <tbody>
                        <tr><th>Kode pesanan</th><td>{{ $order->kode_pesanan }}</td></tr>
                        <tr><th>Program</th><td>{{ $project?->judul ?? '-' }}</td></tr>
                        <tr><th>Area konservasi</th><td>{{ $project?->lokasiHutan?->nama_area_konservasi ?? '-' }}</td></tr>
                        <tr><th>Alamat</th><td>{{ collect([$project?->kelompokTani?->desa, $project?->lokasiHutan?->kabupaten, $project?->lokasiHutan?->provinsi])->filter()->implode(', ') ?: '-' }}</td></tr>
                        <tr><th>Koordinat</th><td>
                            @if ($project?->lokasiHutan?->latitude && $project?->lokasiHutan?->longitude)
                                <a href="https://www.google.com/maps?q={{ $project->lokasiHutan->latitude }},{{ $project->lokasiHutan->longitude }}" target="_blank" rel="noopener">
                                    {{ $project->lokasiHutan->latitude }}, {{ $project->lokasiHutan->longitude }}
                                </a>
                            @else
                                -
                            @endif
                        </td></tr>
                        <tr><th>Patokan lokasi</th><td>{{ $project?->lokasiHutan?->deskripsi ?? '-' }}</td></tr>
                        <tr><th>Kelompok tani</th><td>{{ $project?->kelompokTani?->nama_kelompok ?? '-' }}</td></tr>
                        <tr><th>Jenis pohon</th><td>{{ $project?->jenisPohon?->nama_lokal ?? '-' }}</td></tr>
                        <tr><th>Jumlah bibit</th><td>{{ number_format($order->jumlah_bibit, 0, ',', '.') }} bibit</td></tr>
                        <tr><th>Paket</th><td>{{ $package['name'] ?? 'Adopsi bibit' }}</td></tr>
                        <tr><th>Harga per bibit (setara)</th><td>Rp {{ number_format($order->harga_per_bibit, 0, ',', '.') }}</td></tr>
                        <tr><th>Total bayar</th><td><strong>Rp {{ number_format($order->total_bayar, 0, ',', '.') }}</strong></td></tr>
                        @if ($paymentMethod)
                            <tr><th>Metode pembayaran</th><td>{{ $paymentMethod }}</td></tr>
                            <tr><th>Sumber dana</th><td>{{ $paymentSource ?? '-' }}</td></tr>
                        @endif
                        <tr><th>Status</th><td><span class="status-badge status-{{ $order->status }}">{{ \Illuminate\Support\Str::headline($order->status) }}</span></td></tr>
                        <tr><th>Batas bayar</th><td>{{ $order->batas_bayar?->format('d M Y H:i') ?? '-' }}</td></tr>
                        <tr><th>Dikonfirmasi pada</th><td>{{ $order->dikonfirmasi_pada?->format('d M Y H:i') ?? '-' }}</td></tr>
                    </tbody>
                </table>
            </div>

            @if ($order->status === 'menunggu_pembayaran')
                <div class="panel payment-panel">
                    <h2>Selesaikan sebelum {{ $order->batas_bayar?->format('d M Y H:i') ?? 'batas waktu' }}.</h2>
                    <p>
                        Status ini berarti pesanan belum dibayar penuh. AlamKu
                        <strong>tidak menerapkan DP</strong>: pembayaran harus penuh
                        sebesar Rp {{ number_format($order->total_bayar, 0, ',', '.') }}.
                    </p>
                    <p>
                        Cantumkan kode pesanan <strong>{{ $order->kode_pesanan }}</strong>
                        pada berita transfer. Pesanan lama berstatus ini adalah data demo;
                        hubungi admin bila Anda sudah membayar.
                    </p>
                    <div class="payment-grid">
                        <div><span>Jumlah</span><strong>Rp {{ number_format($order->total_bayar, 0, ',', '.') }}</strong></div>
                        <div><span>Metode</span><strong>Transfer / QRIS</strong></div>
                        <div><span>Status</span><strong>Menunggu pembayaran penuh</strong></div>
                    </div>
                </div>
            @endif

            @unless ($paymentSuccess)
                <div class="panel fund-panel">
                    <div class="panel-head">
                        <div>
                            <h2>Transparansi dana</h2>
                            <p>Rencana pembagian Rp {{ number_format($order->total_bayar, 0, ',', '.') }} untuk adopsi ini.</p>
                        </div>
                    </div>
                    @include('portal.donatur.partials.fund-allocation', [
                        'allocation' => $allocation,
                        'total' => (int) $order->total_bayar,
                    ])
                    <p class="fund-note">Alokasi ini menjadi acuan penggunaan dana setelah pembayaran dikonfirmasi.</p>
                </div>
            @endunless

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

            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h2>Bibit dari adopsi ini.</h2>
                    </div>
                </div>

                @if ($trees->isEmpty())
                    <div class="empty-state">
                        <strong>Belum ada pohon yang dipetakan.</strong>
                        <span>Admin akan menambahkan kode pohon setelah bibit ditanam di lokasi.</span>
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
                                <dl class="tree-facts">
                                    <div><dt>Tanggal tanam</dt><dd>{{ $tree->tanggal_tanam?->format('d M Y') ?? '-' }}</dd></div>
                                    <div><dt>Koordinat</dt><dd>{{ $tree->latitude && $tree->longitude ? $tree->latitude.', '.$tree->longitude : '-' }}</dd></div>
                                </dl>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <aside class="detail-side">
            <h3 style="margin-top:16px">{{ $project?->judul ?? 'Adopsi' }}</h3>
            <p>
                {{ $project?->deskripsi ?? 'Detail program akan tampil di sini setelah data proyek dilengkapi.' }}
            </p>

            <div class="detail-note">
                <div><span>Donatur</span><strong>{{ $user->name }}</strong></div>
                <div><span>Dibuat</span><strong>{{ $order->created_at?->format('d M Y') ?? '-' }}</strong></div>
                <div><span>Pembayaran</span><strong>{{ $order->pembayaran->count() }} catatan</strong></div>
            </div>

            <div class="detail-actions">
                @if ($project)
                    <a class="btn btn-dark" href="{{ route('program.show', $project) }}">Lihat program</a>
                @endif
                <a class="btn" href="{{ route('akun.adoptions') }}">Kembali ke adopsi</a>
                <a class="btn" href="{{ route('program.index') }}">Cari program lain</a>
            </div>
        </aside>
    </div>
@endsection
