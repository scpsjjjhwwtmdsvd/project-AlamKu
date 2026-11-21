@extends('layouts.auth')

@section('title', 'Masuk')

@push('head')
    <link rel="stylesheet" href="{{ asset('css/pages/auth.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/auth-login.css') }}">
@endpush

@section('content')
    <section class="auth-shell">
        <div class="auth-visual">
            <h2>Setiap pohon punya cerita. Mulai dari sini.</h2>
            <p>
                Masuk untuk memantau adopsi, mengelola kelompok tani, atau
                memverifikasi data konservasi di ruang kerja yang sesuai.
            </p>

            <div class="auth-role-grid">
                <div class="auth-role-card">
                    <span class="role-chip role-chip-donatur">Donatur</span>
                    <strong>Adopsi & pantau</strong>
                    <small>Lihat pohon dan laporan dari kontribusi Anda.</small>
                </div>
                <div class="auth-role-card">
                    <span class="role-chip role-chip-mitra">Ruang Tani</span>
                    <strong>Kelola lapangan</strong>
                    <small>Proyek, pohon, dan laporan kelompok tani.</small>
                </div>
                <div class="auth-role-card">
                    <span class="role-chip role-chip-admin">Admin</span>
                    <strong>Verifikasi data</strong>
                    <small>Konsol lengkap untuk seluruh modul AlamKu.</small>
                </div>
            </div>
        </div>

        <div class="auth-card">
            <h1>Selamat datang kembali.</h1>
            <p class="auth-lead">Gunakan email dan kata sandi akun AlamKu Anda.</p>

            <form class="auth-form" method="POST" action="{{ route('login.attempt') }}">
                @csrf

                <div class="form-group">
                    <label for="email">Alamat Email <span class="req">*</span></label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" autofocus>
                    @error('email')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Kata Sandi <span class="req">*</span></label>
                    <input id="password" type="password" name="password" autocomplete="current-password">
                    @error('password')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <label class="check auth-check">
                    <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                    Ingat saya di perangkat ini
                </label>

                <button class="btn btn-dark btn-lg" type="submit" style="width:100%">Masuk ke ruang kerja</button>
            </form>

            <p class="auth-switch">
                Belum punya akun?
                <a href="{{ route('register') }}">Daftar sebagai donatur atau mitra tani</a>
            </p>

            <div class="demo-box">
                <span class="demo-label">Akun demo</span>
                <div class="demo-row"><span>Donatur</span><code>donatur@alamku.test</code></div>
                <div class="demo-row"><span>Mitra Tani</span><code>mitra@alamku.test</code></div>
                <div class="demo-row"><span>Admin</span><code>admin@alamku.test</code></div>
                <div class="demo-row"><span>Kata sandi</span><code>password</code></div>
            </div>
        </div>
    </section>
@endsection
