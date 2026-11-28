@extends('layouts.portal', ['role' => 'mitra'])

@section('title', 'Buat Proyek')

@section('content')
    <section class="portal-hero portal-hero-slim" data-role="mitra">
        <div class="portal-hero-copy">
            <h1>Ajukan proyek baru.</h1>
            <p>Proyek akan tersimpan sebagai draf dan menunggu verifikasi admin sebelum tayang.</p>
        </div>
        <div class="hero-side-actions">
            <a class="btn btn-ghost-light" href="{{ route('mitra.projects') }}">&larr; Kembali</a>
        </div>
    </section>

    @if ($locations->isEmpty())
        <div class="panel empty-state">
            <strong>Belum ada lokasi hutan untuk kelompok Anda.</strong>
            <span>Minta admin menambahkan lokasi hutan ke kelompok {{ $kelompok->nama_kelompok }} sebelum membuat proyek.</span>
            <div style="margin-top:18px">
                <a class="btn btn-dark" href="{{ route('mitra.profile') }}">Periksa profil kelompok</a>
            </div>
        </div>
    @else
        <div class="form-layout">
            <aside class="form-aside">
                <h2>Rencanakan proyek dengan jelas.</h2>
                <p>
                    Jelaskan tujuan konservasi, lokasi tanam, dan kebutuhan bibit agar
                    admin dapat memverifikasi proyek Anda.
                </p>
                <div class="aside-note">
                    Proyek baru berstatus <strong>draf</strong>. Admin akan memeriksa
                    kelengkapan data sebelum mengaktifkannya untuk donatur.
                </div>
                <div class="aside-note">
                    <strong>Kelompok tani</strong><br>
                    {{ $kelompok->nama_kelompok }} &middot; {{ $kelompok->desa }}, {{ $kelompok->kabupaten }}
                </div>
            </aside>

            <div class="form-card">
                <form method="POST" action="{{ route('mitra.projects.store') }}">
                    @csrf

                    <div class="form-grid">
                        <div class="form-group full">
                            <label for="judul">Judul Proyek <span class="req">*</span></label>
                            <input id="judul" type="text" name="judul" value="{{ old('judul') }}" placeholder="Contoh: Restorasi Blok Utara Cikole">
                            @error('judul')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="lokasi_hutan_id">Lokasi Hutan <span class="req">*</span></label>
                            <select id="lokasi_hutan_id" name="lokasi_hutan_id">
                                @foreach ($locations as $location)
                                    <option value="{{ $location->id }}" @selected((string) old('lokasi_hutan_id') === (string) $location->id)>{{ $location->nama_area_konservasi }}</option>
                                @endforeach
                            </select>
                            <small>Hanya lokasi yang terhubung ke kelompok Anda.</small>
                            @error('lokasi_hutan_id')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="jenis_pohon_id">Jenis Pohon <span class="req">*</span></label>
                            <select id="jenis_pohon_id" name="jenis_pohon_id">
                                @foreach ($species as $speciesItem)
                                    <option value="{{ $speciesItem->id }}" @selected((string) old('jenis_pohon_id') === (string) $speciesItem->id)>{{ $speciesItem->nama_lokal }}</option>
                                @endforeach
                            </select>
                            @error('jenis_pohon_id')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="kode_prefix">Kode Prefix Pohon</label>
                            <input id="kode_prefix" type="text" name="kode_prefix" value="{{ old('kode_prefix') }}" maxlength="20" placeholder="Contoh: CKL-MHN" style="text-transform:uppercase">
                            <small>Opsional. Jika kosong, kode pohon dibuat otomatis dari lokasi dan jenis pohon.</small>
                            @error('kode_prefix')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="biaya_per_bibit">Biaya per Bibit (Rp) <span class="req">*</span></label>
                            <input id="biaya_per_bibit" type="number" name="biaya_per_bibit" min="0" step="1" value="{{ old('biaya_per_bibit') }}" placeholder="Contoh: 25000">
                            @error('biaya_per_bibit')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="kuota_bibit">Kuota Bibit <span class="req">*</span></label>
                            <input id="kuota_bibit" type="number" name="kuota_bibit" min="1" step="1" value="{{ old('kuota_bibit') }}" placeholder="Contoh: 500">
                            @error('kuota_bibit')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="tanggal_mulai_tanam">Tanggal Mulai Tanam <span class="req">*</span></label>
                            <input id="tanggal_mulai_tanam" type="date" name="tanggal_mulai_tanam" value="{{ old('tanggal_mulai_tanam', now()->format('Y-m-d')) }}">
                            @error('tanggal_mulai_tanam')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="tanggal_selesai_tanam">Tanggal Selesai Tanam <span class="req">*</span></label>
                            <input id="tanggal_selesai_tanam" type="date" name="tanggal_selesai_tanam" value="{{ old('tanggal_selesai_tanam', now()->addMonths(6)->format('Y-m-d')) }}">
                            @error('tanggal_selesai_tanam')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group full">
                            <label for="deskripsi">Deskripsi Proyek <span class="req">*</span></label>
                            <textarea id="deskripsi" name="deskripsi" rows="5" placeholder="Jelaskan latar belakang, kondisi lahan, dan rencana pelaksanaan proyek.">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group full">
                            <label for="target_ekologi">Target Ekologi <span class="req">*</span></label>
                            <textarea id="target_ekologi" name="target_ekologi" rows="3" placeholder="Contoh: Menanam 500 bibit untuk memperbaiki tutupan lahan dan mencegah erosi.">{{ old('target_ekologi') }}</textarea>
                            @error('target_ekologi')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-actions">
                        <button class="btn btn-dark" type="submit">Ajukan Proyek</button>
                        <a class="btn" href="{{ route('mitra.projects') }}">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection
