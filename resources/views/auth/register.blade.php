@extends('layouts.auth')

@section('title', 'Daftar')

@push('head')
    <link rel="stylesheet" href="{{ asset('css/pages/auth.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/auth-register.css') }}">
@endpush

@section('content')
    <section class="auth-shell">
        <div class="auth-visual">
            <h2>Pilih peran Anda dalam rantai dampak.</h2>
            <p>
                Donatur mengadopsi pohon dan memantau dampaknya. Mitra tani
                mengelola program di lapangan dan mengirim laporan pertumbuhan.
            </p>

            <div class="auth-role-grid">
                <div class="auth-role-card">
                    <span class="role-chip role-chip-donatur">Donatur</span>
                    <strong>Mulai dari satu pohon</strong>
                    <small>Adopsi bibit, ikuti kabar pohon, dan lihat laporan lapangan.</small>
                </div>
                <div class="auth-role-card">
                    <span class="role-chip role-chip-mitra">Mitra Tani</span>
                    <strong>Kelola program lapangan</strong>
                    <small>Daftarkan kelompok, kelola proyek, pohon, dan laporan.</small>
                </div>
            </div>
        </div>

        <div class="auth-card">
            <h1>Buat akun AlamKu.</h1>
            <p class="auth-lead">Pilih peran, lalu lengkapi data yang dibutuhkan.</p>

            <form class="auth-form" method="POST" action="{{ route('register.store') }}">
                @csrf

                <div class="role-tabs" role="radiogroup" aria-label="Pilih peran">
                    <label class="role-tab">
                        <input type="radio" name="role" value="donatur" @checked(old('role', $role ?? 'donatur') === 'donatur')>
                        <span>
                            <strong>Donatur</strong>
                            <small>Adopsi & pantau pohon</small>
                        </span>
                    </label>
                    <label class="role-tab">
                        <input type="radio" name="role" value="mitra" @checked(old('role', $role ?? 'donatur') === 'mitra')>
                        <span>
                            <strong>Mitra Tani</strong>
                            <small>Kelola proyek lapangan</small>
                        </span>
                    </label>
                </div>
                @error('role')
                    <div class="error">{{ $message }}</div>
                @enderror

                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Nama Lengkap <span class="req">*</span></label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" autocomplete="name">
                        @error('name')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="phone">Nomor Telepon</label>
                        <input id="phone" type="text" name="phone" value="{{ old('phone') }}" autocomplete="tel">
                        @error('phone')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group full">
                        <label for="email">Alamat Email <span class="req">*</span></label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email">
                        @error('email')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Kata Sandi <span class="req">*</span></label>
                        <input id="password" type="password" name="password" autocomplete="new-password">
                        <small>Minimal 8 karakter.</small>
                        @error('password')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Ulangi Kata Sandi <span class="req">*</span></label>
                        <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password">
                    </div>
                </div>

                <div class="mitra-fields" data-mitra-fields @if (old('role', $role ?? 'donatur') !== 'mitra') hidden @endif>
                    <div class="form-section-title">
                        <p>Bagian ini wajib diisi jika Anda mendaftar sebagai mitra tani.</p>
                    </div>

                    <div class="form-grid">
                        <div class="form-group full">
                            <label for="nama_kelompok">Nama Kelompok Tani <span class="req">*</span></label>
                            <input id="nama_kelompok" type="text" name="nama_kelompok" value="{{ old('nama_kelompok') }}">
                            @error('nama_kelompok')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="provinsi">Provinsi <span class="req">*</span></label>
                            <input id="provinsi" type="text" name="provinsi" value="{{ old('provinsi') }}">
                            @error('provinsi')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="kabupaten">Kabupaten <span class="req">*</span></label>
                            <input id="kabupaten" type="text" name="kabupaten" value="{{ old('kabupaten') }}">
                            @error('kabupaten')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="desa">Desa <span class="req">*</span></label>
                            <input id="desa" type="text" name="desa" value="{{ old('desa') }}">
                            @error('desa')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="nomor_legalitas">Nomor Legalitas</label>
                            <input id="nomor_legalitas" type="text" name="nomor_legalitas" value="{{ old('nomor_legalitas') }}">
                            @error('nomor_legalitas')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group full">
                            <label for="deskripsi">Deskripsi Kelompok</label>
                            <textarea id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <button class="btn btn-dark btn-lg" type="submit" style="width:100%">Daftar dan masuk</button>
            </form>

            <p class="auth-switch">
                Sudah punya akun?
                <a href="{{ route('login') }}">Masuk ke ruang kerja</a>
            </p>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        (() => {
            const fields = document.querySelector('[data-mitra-fields]');
            const roles = document.querySelectorAll('input[name="role"]');

            const sync = () => {
                const mitra = document.querySelector('input[name="role"]:checked')?.value === 'mitra';
                fields.hidden = !mitra;
            };

            roles.forEach((role) => role.addEventListener('change', sync));
            sync();
        })();
    </script>
@endpush
