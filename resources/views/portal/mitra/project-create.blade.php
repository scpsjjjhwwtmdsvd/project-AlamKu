@extends('layouts.portal', ['role' => 'mitra'])

@section('title', 'Buat Proyek')

@section('content')
    @php
        $createNewLocation = $locations->isEmpty() || (bool) old('new_location');
    @endphp

    <section class="portal-hero portal-hero-slim" data-role="mitra">
        <div class="portal-hero-copy">
            <h1>Ajukan proyek baru.</h1>
            <p>Proyek akan tersimpan sebagai draf dan menunggu verifikasi admin sebelum tayang.</p>
        </div>
        <div class="hero-side-actions">
            <a class="btn btn-ghost-light" href="{{ route('mitra.projects') }}">&larr; Kembali</a>
        </div>
    </section>

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

                    <div class="form-group full">
                        <label for="lokasi_hutan_id">Lokasi Hutan <span class="req">*</span></label>

                        @if ($locations->isNotEmpty())
                            <div id="existing-location-fields" @if ($createNewLocation) hidden @endif>
                                <select id="lokasi_hutan_id" name="lokasi_hutan_id">
                                    @foreach ($locations as $location)
                                        <option value="{{ $location->id }}" @selected((string) old('lokasi_hutan_id') === (string) $location->id)>{{ $location->nama_area_konservasi }}</option>
                                    @endforeach
                                </select>
                                <small>Pilih lokasi hutan yang sudah terdaftar untuk kelompok Anda.</small>
                            </div>

                            <label class="check" style="margin-top:10px">
                                <input id="new_location" type="checkbox" name="new_location" value="1" @checked($createNewLocation)>
                                Tambah lokasi hutan baru
                            </label>
                        @else
                            <input type="hidden" name="new_location" value="1">
                            <small>Kelompok Anda belum punya lokasi hutan. Lengkapi data lokasi baru di bawah ini.</small>
                        @endif

                        @error('lokasi_hutan_id')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div id="new-location-fields" class="form-grid full" @if (! $createNewLocation) hidden @endif>
                        <div class="form-group full">
                            <label for="nama_area_konservasi">Nama Area Konservasi <span class="req">*</span></label>
                            <input id="nama_area_konservasi" type="text" name="nama_area_konservasi" value="{{ old('nama_area_konservasi') }}" placeholder="Contoh: Hutan Adat Singakerta">
                            @error('nama_area_konservasi')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="provinsi">Provinsi <span class="req">*</span></label>
                            <input id="provinsi" type="text" name="provinsi" value="{{ old('provinsi') }}" placeholder="Contoh: Bali">
                            @error('provinsi')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="kabupaten">Kabupaten <span class="req">*</span></label>
                            <input id="kabupaten" type="text" name="kabupaten" value="{{ old('kabupaten') }}" placeholder="Contoh: Gianyar">
                            @error('kabupaten')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="latitude">Latitude <span class="req">*</span></label>
                            <input id="latitude" type="number" step="0.0000001" name="latitude" value="{{ old('latitude') }}" placeholder="Contoh: -8.5069000">
                            @error('latitude')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="longitude">Longitude <span class="req">*</span></label>
                            <input id="longitude" type="number" step="0.0000001" name="longitude" value="{{ old('longitude') }}" placeholder="Contoh: 115.2625000">
                            @error('longitude')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="luas_hektar">Luas Hutan (hektar)</label>
                            <input id="luas_hektar" type="number" step="0.01" min="0" name="luas_hektar" value="{{ old('luas_hektar') }}" placeholder="Contoh: 12.5">
                            @error('luas_hektar')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group full">
                            <label for="deskripsi_lokasi">Deskripsi Lokasi</label>
                            <textarea id="deskripsi_lokasi" name="deskripsi_lokasi" rows="3" placeholder="Kondisi lahan, akses, atau catatan penting tentang lokasi ini.">{{ old('deskripsi_lokasi') }}</textarea>
                            @error('deskripsi_lokasi')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
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
@endsection

@push('scripts')
    <script>
        (() => {
            const toggle = document.getElementById('new_location');
            const existing = document.getElementById('existing-location-fields');
            const fields = document.getElementById('new-location-fields');

            if (!toggle || !existing || !fields) return;

            const sync = () => {
                existing.hidden = toggle.checked;
                fields.hidden = !toggle.checked;
            };

            toggle.addEventListener('change', sync);
            sync();
        })();
    </script>
@endpush
