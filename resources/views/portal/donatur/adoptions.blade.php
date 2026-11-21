@extends('layouts.portal', ['role' => 'donatur'])

@section('title', 'Adopsi Saya')

@section('content')
    <section class="portal-hero portal-hero-slim" data-role="donatur">
        <div class="portal-hero-copy">
            <h1>Semua adopsi Anda.</h1>
            <p>Pantau status pesanan, batas pembayaran, dan nilai kontribusi dari setiap program.</p>
        </div>
        <div class="hero-side-actions">
            <a class="btn btn-light" href="{{ route('program.index') }}">Adopsi program baru</a>
        </div>
    </section>

    <section class="portal-stats">
        <div class="portal-stat">
            <span class="portal-stat-label">Total adopsi</span>
            <strong>{{ number_format($summary['total'], 0, ',', '.') }}</strong>
            <small>Pesanan tercatat</small>
        </div>
        <div class="portal-stat">
            <span class="portal-stat-label">Bibit terkonfirmasi</span>
            <strong>{{ number_format($summary['trees'], 0, ',', '.') }}</strong>
            <small>Bibit yang sudah dibayar</small>
        </div>
        <div class="portal-stat">
            <span class="portal-stat-label">Nilai terkonfirmasi</span>
            <strong>Rp {{ number_format($summary['paid'], 0, ',', '.') }}</strong>
            <small>Total kontribusi</small>
        </div>
    </section>

    <p class="section-label">Daftar pesanan</p>

    <div class="data-card">
        @if ($orders->isEmpty())
            <div class="empty-state">
                <strong>Belum ada adopsi.</strong>
                <span>Setiap adopsi dimulai dari satu program. Jelajahi program yang aktif sekarang.</span>
                <div style="margin-top:18px">
                    <a class="btn btn-dark" href="{{ route('program.index') }}">Jelajahi program</a>
                </div>
            </div>
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Program</th>
                            <th>Bibit</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Batas Bayar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td data-label="Kode">{{ $order->kode_pesanan }}</td>
                                <td data-label="Program">{{ $order->proyek?->judul ?? '-' }}</td>
                                <td data-label="Bibit">{{ number_format($order->jumlah_bibit, 0, ',', '.') }}</td>
                                <td data-label="Total">Rp {{ number_format($order->total_bayar, 0, ',', '.') }}</td>
                                <td data-label="Status">
                                    <span class="status-badge status-{{ $order->status }}">{{ \Illuminate\Support\Str::headline($order->status) }}</span>
                                </td>
                                <td data-label="Batas Bayar">{{ $order->batas_bayar?->format('d M Y H:i') ?? '-' }}</td>
                                <td data-label="Aksi">
                                    <div class="actions">
                                        <a class="btn btn-sm" href="{{ route('akun.adoptions.show', $order->id) }}">Lihat</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="pagination">
        {{ $orders->links() }}
    </div>
@endsection
