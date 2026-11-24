@extends('layouts.app')

@section('title', $title)

@section('content')
    @php
        $descriptions = [
            'kelompok-tani' => 'Kelola profil kelompok tani, legalitas, dan status verifikasi mitra.',
            'lokasi-hutan' => 'Daftarkan area konservasi beserta koordinat dan luasnya.',
            'jenis-pohon' => 'Kelola spesies pohon dan estimasi serapan karbon per tahun.',
            'proyek' => 'Kelola program tanam, target ekologi, dan kuota bibit.',
            'alokasi-dana' => 'Catat rencana dan realisasi anggaran setiap proyek.',
            'pesanan-adopsi' => 'Kelola pesanan adopsi, donatur, dan status pembayaran.',
            'pohon' => 'Pantau pohon yang diadopsi, koordinat, dan status kesehatannya.',
            'pembayaran' => 'Catat transaksi pembayaran dan respons gateway.',
            'laporan-lapangan' => 'Dokumentasikan pertumbuhan dan kondisi pohon di lapangan.',
            'foto-geotag' => 'Kelola foto lapangan beserta koordinat dan waktu pengambilan.',
            'log-perubahan' => 'Jejak audit perubahan data untuk kebutuhan transparansi.',
        ];

        $description = $descriptions[$routePrefix] ?? 'Kelola data '.$title.' AlamKu.';
    @endphp

    <section class="admin-hero" data-module="{{ $routePrefix }}">
        <div>
            <h1>{{ $title }}</h1>
            <p>{{ $description }}</p>
            <div class="admin-hero-meta">
                <span class="admin-pill">{{ number_format($items->total(), 0, ',', '.') }} data</span>
                <span class="admin-pill">Halaman {{ $items->currentPage() }}</span>
                <span class="admin-pill">Diperbarui {{ now()->translatedFormat('d M Y') }}</span>
            </div>
        </div>

        <a class="btn btn-light" href="{{ route($routePrefix.'.create') }}">+ Tambah {{ $title }}</a>
    </section>

    <p class="section-label">Daftar data</p>

    <div class="data-card">
        @if ($items->isEmpty())
            <div class="empty-state">
                <strong>Belum ada data {{ strtolower($title) }}.</strong>
                <span>Mulai dengan menambahkan data pertama lewat tombol di atas.</span>
            </div>
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            @foreach ($headers as $header)
                                <th>{{ $header }}</th>
                            @endforeach
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr>
                                @foreach ($row['cells'] as $index => $cell)
                                    <td data-label="{{ $headers[$index] ?? '' }}">
                                        @if ($row['images'][$index] ?? null)
                                            <img class="row-thumb" src="{{ $row['images'][$index] }}" alt="{{ $headers[$index] ?? 'Foto' }}" loading="lazy">
                                        @else
                                            {{ $cell }}
                                        @endif
                                    </td>
                                @endforeach
                                <td data-label="Aksi">
                                    <div class="actions">
                                        <a class="btn btn-sm" href="{{ route($routePrefix.'.show', $row['id']) }}">Lihat</a>
                                        <a class="btn btn-sm" href="{{ route($routePrefix.'.edit', $row['id']) }}">Edit</a>
                                        <form method="POST" action="{{ route($routePrefix.'.destroy', $row['id']) }}" onsubmit="return confirm('Hapus data ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger" type="submit">Hapus</button>
                                        </form>
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
        {{ $items->links() }}
    </div>
@endsection
