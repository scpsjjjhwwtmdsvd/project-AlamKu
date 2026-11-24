@extends('layouts.portal', ['role' => 'mitra'])

@section('title', $proyek->judul)

@section('content')
    @php
        $percent = (int) $proyek->kuota_bibit > 0
            ? min(100, (int) round(($proyek->bibit_dipesan / $proyek->kuota_bibit) * 100))
            : 0;
        $remaining = max(0, (int) $proyek->kuota_bibit - (int) $proyek->bibit_dipesan);
    @endphp

    <section class="portal-hero portal-hero-slim" data-role="mitra">
        <div class="portal-hero-copy">
            <span class="pill">{{ ucfirst(str_replace('_', ' ', $proyek->status)) }}</span>
            <h1>{{ $proyek->judul }}</h1>
            <p>{{ $proyek->lokasiHutan?->nama_area_konservasi ?? 'Lokasi belum diisi' }} &middot; {{ $proyek->jenisPohon?->nama_lokal ?? 'Jenis pohon belum diisi' }}</p>
            <div class="admin-hero-meta">
                <span class="admin-pill">{{ number_format($proyek->bibit_dipesan, 0, ',', '.') }} / {{ number_format($proyek->kuota_bibit, 0, ',', '.') }} bibit</span>
                <span class="admin-pill">Rp {{ number_format($fund['received'], 0, ',', '.') }} diterima</span>
            </div>
        </div>
        <div class="hero-side-actions">
            <a class="btn btn-ghost-light" href="{{ route('mitra.projects') }}">&larr; Semua proyek</a>
        </div>
    </section>

    <div class="detail-layout">
        <div class="detail-stack">
            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h2>Proses &amp; status proyek.</h2>
                        <p>Perbarui status saat penanaman selesai. Status kuota penuh tetap ditentukan oleh sistem adopsi.</p>
                    </div>
                </div>
                <form class="inline-status-form" method="POST" action="{{ route('mitra.projects.status', $proyek) }}">
                    @csrf
                    @method('PUT')
                    <select name="status" aria-label="Status proses {{ $proyek->judul }}">
                        <option value="aktif" @selected($proyek->status === 'aktif')>Aktif — penanaman berjalan</option>
                        <option value="selesai" @selected($proyek->status === 'selesai')>Selesai — penanaman tuntas</option>
                    </select>
                    <button class="btn btn-dark btn-sm" type="submit">Simpan status</button>
                </form>
                @if ($proyek->status === 'penuh')
                    <p class="fund-note">Proyek berstatus penuh karena kuota bibit sudah terisi. Anda tetap dapat menandainya selesai setelah penanaman tuntas.</p>
                @endif
                <div class="progress" aria-hidden="true" style="margin-top:16px">
                    <span @style(['width: ' . $percent . '%'])></span>
                </div>
                <p class="fund-note">{{ number_format($remaining, 0, ',', '.') }} bibit tersisa dari kuota.</p>
            </div>

            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h2>Notifikasi donasi.</h2>
                        <p>Donasi terkonfirmasi otomatis pada mode demo; tidak perlu persetujuan admin.</p>
                    </div>
                </div>

                @if ($donations->isEmpty())
                    <div class="empty-state">
                        <strong>Belum ada donasi.</strong>
                        <span>Donasi yang dibayar akan muncul di sini.</span>
                    </div>
                @else
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr><th>Donatur</th><th>Bibit</th><th>Nilai</th><th>Waktu</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($donations as $donation)
                                    <tr>
                                        <td data-label="Donatur">{{ $donation->donatur?->name ?? 'Donatur' }}</td>
                                        <td data-label="Bibit">{{ number_format($donation->jumlah_bibit, 0, ',', '.') }}</td>
                                        <td data-label="Nilai">Rp {{ number_format($donation->total_bayar, 0, ',', '.') }}</td>
                                        <td data-label="Waktu">{{ $donation->dikonfirmasi_pada?->format('d M Y H:i') ?? '-' }} WIB</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h2>Rincian dana.</h2>
                        <p>Dana diterima Rp {{ number_format($fund['received'], 0, ',', '.') }} dari donasi terkonfirmasi.</p>
                    </div>
                </div>

                @if ($proyek->alokasiDana->isEmpty())
                    <div class="empty-state">
                        <strong>Alokasi dana belum dicatat.</strong>
                        <span>Admin akan menambahkan rencana penggunaan dana untuk proyek ini.</span>
                    </div>
                @else
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr><th>Kategori</th><th>Rencana</th><th>Realisasi</th><th>Keterangan</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($proyek->alokasiDana as $allocation)
                                    <tr>
                                        <td data-label="Kategori">{{ $allocation->kategori }}</td>
                                        <td data-label="Rencana">Rp {{ number_format($allocation->rencana, 0, ',', '.') }}</td>
                                        <td data-label="Realisasi">Rp {{ number_format($allocation->realisasi, 0, ',', '.') }}</td>
                                        <td data-label="Keterangan">{{ $allocation->keterangan ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Total</th>
                                    <th>Rp {{ number_format($fund['planned'], 0, ',', '.') }}</th>
                                    <th>Rp {{ number_format($fund['realized'], 0, ',', '.') }}</th>
                                    <th>Sisa rencana Rp {{ number_format(max(0, $fund['planned'] - $fund['realized']), 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>

            <div class="panel">
                <div class="panel-head">
                    <div><h2>Pohon di proyek ini.</h2></div>
                </div>

                @if ($trees->isEmpty())
                    <div class="empty-state">
                        <strong>Belum ada pohon terdaftar.</strong>
                        <span>Admin akan memetakan bibit ke kode pohon setelah penanaman.</span>
                    </div>
                @else
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr><th>Kode</th><th>Jenis</th><th>Status</th><th>Perbarui</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($trees as $tree)
                                    <tr>
                                        <td data-label="Kode">{{ $tree->kode_pohon }}</td>
                                        <td data-label="Jenis">{{ $tree->jenisPohon?->nama_lokal ?? '-' }}</td>
                                        <td data-label="Status">
                                            <span class="status-badge status-{{ $tree->status_kesehatan }}">{{ \Illuminate\Support\Str::headline($tree->status_kesehatan) }}</span>
                                        </td>
                                        <td data-label="Perbarui">
                                            <form class="inline-status-form" method="POST" action="{{ route('mitra.trees.status', $tree) }}">
                                                @csrf
                                                @method('PUT')
                                                <select name="status_kesehatan" aria-label="Status {{ $tree->kode_pohon }}">
                                                    @foreach (['belum_ditanam' => 'Belum Ditanam', 'tumbuh_baik' => 'Tumbuh Baik', 'perlu_penyulaman' => 'Perlu Penyulaman', 'mati' => 'Mati'] as $value => $label)
                                                        <option value="{{ $value }}" @selected($tree->status_kesehatan === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                <button class="btn btn-sm" type="submit">Simpan</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="panel">
                <div class="panel-head">
                    <div><h2>Laporan lapangan terbaru.</h2></div>
                    <a class="btn btn-sm" href="{{ route('mitra.reports.create') }}">Buat laporan</a>
                </div>

                @if ($reports->isEmpty())
                    <div class="empty-state">
                        <strong>Belum ada laporan.</strong>
                        <span>Kirim laporan pertama untuk proyek ini.</span>
                    </div>
                @else
                    <div class="report-list">
                        @foreach ($reports as $report)
                            <article class="report-item">
                                <div class="report-main">
                                    <span class="status-badge status-{{ $report->status }}">{{ \Illuminate\Support\Str::headline($report->status) }}</span>
                                    <h3>{{ $report->judul }}</h3>
                                    <p>{{ $report->tanggal_laporan?->format('d M Y') ?? '-' }} &middot; {{ $report->pohon?->kode_pohon ?? 'Semua pohon' }}</p>
                                </div>
                                <div class="report-side">
                                    <span class="report-condition">{{ \Illuminate\Support\Str::headline($report->kondisi) }}</span>
                                    @if ($report->status === 'draft')
                                        <a class="btn btn-sm" href="{{ route('mitra.reports.edit', $report) }}">Ubah</a>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <aside class="detail-side">
            <h3 style="margin-top:16px">Detail proyek</h3>
            <div class="detail-note">
                <div><span>Status</span><strong>{{ ucfirst(str_replace('_', ' ', $proyek->status)) }}</strong></div>
                <div><span>Mulai tanam</span><strong>{{ $proyek->tanggal_mulai_tanam?->format('d M Y') ?? '-' }}</strong></div>
                <div><span>Selesai tanam</span><strong>{{ $proyek->tanggal_selesai_tanam?->format('d M Y') ?? '-' }}</strong></div>
                <div><span>Kuota</span><strong>{{ number_format($proyek->kuota_bibit, 0, ',', '.') }} bibit</strong></div>
                <div><span>Dana diterima</span><strong>Rp {{ number_format($fund['received'], 0, ',', '.') }}</strong></div>
            </div>
            <div class="detail-note">
                <div><span>Provinsi</span><strong>{{ $proyek->lokasiHutan?->provinsi ?? '-' }}</strong></div>
                <div><span>Kabupaten</span><strong>{{ $proyek->lokasiHutan?->kabupaten ?? '-' }}</strong></div>
                <div><span>Desa</span><strong>{{ $proyek->kelompokTani?->desa ?? '-' }}</strong></div>
                <div><span>Koordinat</span><strong>{{ $proyek->lokasiHutan?->latitude && $proyek->lokasiHutan?->longitude ? $proyek->lokasiHutan->latitude.', '.$proyek->lokasiHutan->longitude : '-' }}</strong></div>
            </div>
            <p class="fund-note">{{ $proyek->deskripsi }}</p>
            <div class="detail-actions">
                <a class="btn" href="{{ route('mitra.trees') }}">Lihat semua pohon</a>
                <a class="btn" href="{{ route('mitra.reports') }}">Lihat semua laporan</a>
            </div>
        </aside>
    </div>
@endsection
