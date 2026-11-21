@extends('layouts.portal', ['role' => 'donatur'])

@section('title', 'Detail Adopsi')

@section('content')
    @php
        $project = $order->proyek;
    @endphp

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

    <div class="detail-layout">
        <div class="detail-stack">
            <div class="data-card">
                <table class="detail-table">
                    <tbody>
                        <tr><th>Kode pesanan</th><td>{{ $order->kode_pesanan }}</td></tr>
                        <tr><th>Program</th><td>{{ $project?->judul ?? '-' }}</td></tr>
                        <tr><th>Lokasi</th><td>{{ $project?->lokasiHutan?->nama_area_konservasi ?? '-' }}</td></tr>
                        <tr><th>Kelompok tani</th><td>{{ $project?->kelompokTani?->nama_kelompok ?? '-' }}</td></tr>
                        <tr><th>Jenis pohon</th><td>{{ $project?->jenisPohon?->nama_lokal ?? '-' }}</td></tr>
                        <tr><th>Jumlah bibit</th><td>{{ number_format($order->jumlah_bibit, 0, ',', '.') }} bibit</td></tr>
                        <tr><th>Harga per bibit</th><td>Rp {{ number_format($order->harga_per_bibit, 0, ',', '.') }}</td></tr>
                        <tr><th>Total bayar</th><td><strong>Rp {{ number_format($order->total_bayar, 0, ',', '.') }}</strong></td></tr>
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
                        Pembayaran diverifikasi oleh admin AlamKu. Cantumkan kode pesanan
                        <strong>{{ $order->kode_pesanan }}</strong> pada berita transfer.
                    </p>
                    <div class="payment-grid">
                        <div><span>Jumlah</span><strong>Rp {{ number_format($order->total_bayar, 0, ',', '.') }}</strong></div>
                        <div><span>Metode</span><strong>Transfer / QRIS</strong></div>
                        <div><span>Status</span><strong>Menunggu verifikasi</strong></div>
                    </div>
                    <p class="muted" style="margin-top:16px">
                        Hubungi admin melalui kanal resmi AlamKu setelah melakukan pembayaran
                        agar pesanan dapat dikonfirmasi.
                    </p>
                </div>
            @endif

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
