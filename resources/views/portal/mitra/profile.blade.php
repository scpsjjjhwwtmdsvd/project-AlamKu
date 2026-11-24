@extends('layouts.portal', ['role' => 'mitra'])

@section('title', 'Profil Tani')

@section('content')
    <section class="portal-hero portal-hero-slim" data-role="mitra">
        <div class="portal-hero-copy">
            <h1>Identitas kelompok tani.</h1>
            <p>Data ini digunakan admin untuk memverifikasi mitra dan menghubungkan proyek.</p>
            @if ($kelompok)
                <div class="admin-hero-meta">
                    <span class="admin-pill">{{ $kelompok->terverifikasi ? 'Terverifikasi' : 'Menunggu verifikasi' }}</span>
                    <span class="admin-pill">{{ $kelompok->desa }}, {{ $kelompok->kabupaten }}</span>
                </div>
            @endif
        </div>
    </section>

    @unless ($kelompok)
        <div class="alert error">
            Profil kelompok tani belum ada. Lengkapi formulir di bawah untuk membuat profil mitra.
        </div>
    @endunless

    <div class="form-layout">
        <aside class="form-aside">
            <h2>{{ $kelompok?->nama_kelompok ?? 'Kelompok baru' }}</h2>
            <p>
                Perbarui data kelompok agar admin dapat memverifikasi legalitas
                dan menghubungkan program konservasi yang sesuai.
            </p>
            <div class="aside-note">
                Status verifikasi hanya dapat diubah oleh admin. Pastikan nomor
                legalitas dan alamat kelompok sudah benar.
            </div>
        </aside>

        <div class="form-card">
            <form method="POST" action="{{ route('mitra.profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')


                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Nama Lengkap <span class="req">*</span></label>
                        <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}">
                        @error('name')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="phone">Nomor Telepon</label>
                        <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}">
                        @error('phone')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group full">
                        <label for="email">Alamat Email <span class="req">*</span></label>
                        <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}">
                        @error('email')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-section-title">
                </div>

                <div class="form-grid">
                    <div class="form-group full">
                        <label for="nama_kelompok">Nama Kelompok <span class="req">*</span></label>
                        <input id="nama_kelompok" type="text" name="nama_kelompok" value="{{ old('nama_kelompok', $kelompok?->nama_kelompok) }}">
                        @error('nama_kelompok')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="provinsi">Provinsi <span class="req">*</span></label>
                        <input id="provinsi" type="text" name="provinsi" value="{{ old('provinsi', $kelompok?->provinsi) }}">
                        @error('provinsi')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="kabupaten">Kabupaten <span class="req">*</span></label>
                        <input id="kabupaten" type="text" name="kabupaten" value="{{ old('kabupaten', $kelompok?->kabupaten) }}">
                        @error('kabupaten')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="desa">Desa <span class="req">*</span></label>
                        <input id="desa" type="text" name="desa" value="{{ old('desa', $kelompok?->desa) }}">
                        @error('desa')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="nomor_legalitas">Nomor Legalitas</label>
                        <input id="nomor_legalitas" type="text" name="nomor_legalitas" value="{{ old('nomor_legalitas', $kelompok?->nomor_legalitas) }}">
                        @error('nomor_legalitas')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group full">
                        <label for="foto_profil">Foto Profil Kelompok</label>
                        @if ($kelompok?->foto_profil)
                            <div class="file-preview">
                                <img
                                    src="{{ \Illuminate\Support\Str::startsWith($kelompok->foto_profil, ['http://', 'https://', '//']) ? $kelompok->foto_profil : asset($kelompok->foto_profil) }}"
                                    alt="Foto {{ $kelompok->nama_kelompok }}"
                                    loading="lazy"
                                >
                                <span>Foto saat ini. Pilih berkas baru untuk menggantinya.</span>
                            </div>
                        @endif
                        <input id="foto_profil" type="file" name="foto_profil" accept="image/*">
                        <small>Unggah JPG, PNG, atau WEBP maksimal 4 MB agar kelompok Anda mudah dikenali admin.</small>
                        @error('foto_profil')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group full">
                        <label for="deskripsi">Deskripsi Kelompok</label>
                        <textarea id="deskripsi" name="deskripsi" rows="4">{{ old('deskripsi', $kelompok?->deskripsi) }}</textarea>
                        @error('deskripsi')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-section-title">
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="password">Kata Sandi Baru</label>
                        <input id="password" type="password" name="password" autocomplete="new-password">
                        <small>Minimal 8 karakter, kosongkan jika tidak diubah.</small>
                        @error('password')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Ulangi Kata Sandi Baru</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password">
                    </div>
                </div>

                <div class="form-actions">
                    <button class="btn btn-dark" type="submit">Simpan Profil</button>
                    <a class="btn" href="{{ route('mitra.dashboard') }}">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
