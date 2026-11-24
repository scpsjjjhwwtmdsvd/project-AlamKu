@extends('layouts.portal', ['role' => 'mitra'])

@section('title', 'Proyek Tani')

@section('content')
    @php
        $projectImages = [
            'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1511497584788-876760111969?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=1000&q=80',
        ];
    @endphp

    <section class="portal-hero portal-hero-slim" data-role="mitra">
        <div class="portal-hero-copy">
            <h1>Program kelompok Anda.</h1>
            <p>Setiap proyek menghubungkan lokasi hutan, jenis pohon, dan donatur yang mendukung.</p>
        </div>
        <div class="hero-side-actions">
            <a class="btn btn-light" href="{{ route('mitra.reports.create') }}">Buat laporan</a>
        </div>
    </section>

    @if ($projects->isEmpty())
        <div class="panel empty-state">
            <strong>Belum ada proyek.</strong>
            <span>Admin belum menghubungkan program konservasi ke kelompok {{ $kelompok?->nama_kelompok ?? 'Anda' }}.</span>
        </div>
    @else
        <div class="project-grid">
            @foreach ($projects as $project)
                @php
                    $percent = (int) $project->kuota_bibit > 0
                        ? min(100, (int) round(($project->bibit_dipesan / $project->kuota_bibit) * 100))
                        : 0;
                @endphp
                <a class="project-card" href="{{ route('mitra.projects.show', $project) }}">
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
                        <dl class="project-facts">
                            <div><dt>Mulai tanam</dt><dd>{{ $project->tanggal_mulai_tanam?->format('d M Y') ?? '-' }}</dd></div>
                            <div><dt>Selesai tanam</dt><dd>{{ $project->tanggal_selesai_tanam?->format('d M Y') ?? '-' }}</dd></div>
                            <div><dt>Biaya / bibit</dt><dd>Rp {{ number_format($project->biaya_per_bibit, 0, ',', '.') }}</dd></div>
                            <div><dt>Kuota</dt><dd>{{ number_format($project->kuota_bibit, 0, ',', '.') }} bibit</dd></div>
                        </dl>
                        <small class="card-link-hint">Buka detail proses, status, dan dana &rarr;</small>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="pagination">
            {{ $projects->links() }}
        </div>
    @endif
@endsection
