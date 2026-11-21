@extends('layouts.portal', ['role' => 'mitra'])

@section('title', 'Buat Laporan')

@section('content')
    <section class="portal-hero portal-hero-slim" data-role="mitra">
        <div class="portal-hero-copy">
            <h1>Catat kondisi lapangan.</h1>
            <p>Laporan akan disimpan sebagai draf dan menunggu verifikasi admin.</p>
        </div>
        <div class="hero-side-actions">
            <a class="btn btn-ghost-light" href="{{ route('mitra.reports') }}">&larr; Kembali</a>
        </div>
    </section>

    @if ($projects->isEmpty())
        <div class="panel empty-state">
            <strong>Belum ada proyek untuk dilaporkan.</strong>
            <span>Laporan lapangan membutuhkan proyek yang terhubung ke kelompok tani Anda.</span>
            <div style="margin-top:18px">
                <a class="btn btn-dark" href="{{ route('mitra.profile') }}">Periksa profil kelompok</a>
            </div>
        </div>
    @else
        <div class="form-layout">
            <aside class="form-aside">
                <h2>Data yang baik, dampak yang jelas.</h2>
                <p>
                    Tuliskan kondisi nyata di lapangan. Sertakan tinggi, diameter,
                    dan tanggal pemantauan bila tersedia.
                </p>
                <div class="aside-note">
                    Laporan berstatus draf sampai admin memverifikasi. Anda tetap
                    dapat melihat seluruh riwayat laporan di halaman Laporan.
                </div>
            </aside>

            <div class="form-card">
                <form method="POST" action="{{ route('mitra.reports.store') }}">
                    @csrf

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="proyek_id">Proyek <span class="req">*</span></label>
                            <select id="proyek_id" name="proyek_id" data-project-select>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}" @selected((string) old('proyek_id') === (string) $project->id)>{{ $project->judul }}</option>
                                @endforeach
                            </select>
                            @error('proyek_id')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="pohon_id">Pohon (opsional)</label>
                            <select id="pohon_id" name="pohon_id" data-tree-select>
                                <option value="">- Tidak spesifik -</option>
                                @foreach ($trees as $tree)
                                    <option value="{{ $tree->id }}" data-project="{{ $tree->proyek_id }}" @selected((string) old('pohon_id') === (string) $tree->id)>{{ $tree->kode_pohon }}</option>
                                @endforeach
                            </select>
                            @error('pohon_id')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group full">
                            <label for="judul">Judul Laporan <span class="req">*</span></label>
                            <input id="judul" type="text" name="judul" value="{{ old('judul') }}" placeholder="Contoh: Pemantauan bulanan blok utara">
                            @error('judul')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="kondisi">Kondisi <span class="req">*</span></label>
                            <select id="kondisi" name="kondisi">
                                <option value="sehat" @selected(old('kondisi', 'sehat') === 'sehat')>Sehat</option>
                                <option value="perlu_perawatan" @selected(old('kondisi') === 'perlu_perawatan')>Perlu Perawatan</option>
                                <option value="kritis" @selected(old('kondisi') === 'kritis')>Kritis</option>
                            </select>
                            @error('kondisi')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="tanggal_laporan">Tanggal Laporan <span class="req">*</span></label>
                            <input id="tanggal_laporan" type="date" name="tanggal_laporan" value="{{ old('tanggal_laporan', now()->format('Y-m-d')) }}">
                            @error('tanggal_laporan')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="tinggi_cm">Tinggi (cm)</label>
                            <input id="tinggi_cm" type="number" name="tinggi_cm" min="0" value="{{ old('tinggi_cm') }}">
                            @error('tinggi_cm')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="diameter_cm">Diameter (cm)</label>
                            <input id="diameter_cm" type="number" step="0.01" min="0" name="diameter_cm" value="{{ old('diameter_cm') }}">
                            @error('diameter_cm')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group full">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea id="deskripsi" name="deskripsi" rows="5" placeholder="Catatan pertumbuhan, hama, cuaca, atau tindakan yang dilakukan.">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-actions">
                        <button class="btn btn-dark" type="submit">Kirim Laporan</button>
                        <a class="btn" href="{{ route('mitra.reports') }}">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        (() => {
            const projectSelect = document.querySelector('[data-project-select]');
            const treeSelect = document.querySelector('[data-tree-select]');

            if (!projectSelect || !treeSelect) return;

            const options = [...treeSelect.querySelectorAll('option[data-project]')];

            const sync = () => {
                const project = projectSelect.value;
                let selectedStillValid = !treeSelect.value;

                options.forEach((option) => {
                    const match = option.dataset.project === project;
                    option.hidden = !match;
                    option.disabled = !match;
                    if (match && option.selected) selectedStillValid = true;
                });

                if (!selectedStillValid) treeSelect.value = '';
            };

            projectSelect.addEventListener('change', sync);
            sync();
        })();
    </script>
@endpush
