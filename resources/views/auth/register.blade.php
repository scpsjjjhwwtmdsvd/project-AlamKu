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
                        <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" inputmode="numeric" pattern="[0-9]*" maxlength="20">
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
                        <div class="password-field">
                            <input id="password" type="password" name="password" autocomplete="new-password">
                            <button
                                class="password-toggle"
                                type="button"
                                data-password-toggle="password"
                                aria-label="Tampilkan kata sandi"
                                aria-pressed="false"
                            >
                                <svg class="icon-eye" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg class="icon-eye-off" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                                    <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c6.5 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                                    <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3.5 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                                    <line x1="2" y1="2" x2="22" y2="22"/>
                                </svg>
                            </button>
                        </div>
                        <small>Minimal 8 karakter.</small>
                        @error('password')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Ulangi Kata Sandi <span class="req">*</span></label>
                        <div class="password-field">
                            <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password">
                            <button
                                class="password-toggle"
                                type="button"
                                data-password-toggle="password_confirmation"
                                aria-label="Tampilkan ulangi kata sandi"
                                aria-pressed="false"
                            >
                                <svg class="icon-eye" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg class="icon-eye-off" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                                    <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c6.5 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                                    <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3.5 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                                    <line x1="2" y1="2" x2="22" y2="22"/>
                                </svg>
                            </button>
                        </div>
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

            const phone = document.getElementById('phone');

            if (phone) {
                phone.addEventListener('input', () => {
                    const digitsOnly = phone.value.replace(/[^0-9]/g, '');

                    if (phone.value !== digitsOnly) {
                        phone.value = digitsOnly;
                    }
                });
            }

            document.querySelectorAll('[data-password-toggle]').forEach((button) => {
                button.addEventListener('click', () => {
                    const input = document.getElementById(button.dataset.passwordToggle);
                    if (!input) return;

                    const showing = input.type === 'text';
                    input.type = showing ? 'password' : 'text';
                    button.setAttribute('aria-pressed', String(!showing));
                    button.setAttribute('aria-label', showing ? 'Tampilkan kata sandi' : 'Sembunyikan kata sandi');
                });
            });
        })();
    </script>
@endpush
