@extends('layouts.portal', ['role' => 'donatur'])

@section('title', 'Profil Donatur')

@section('content')
    <section class="portal-hero portal-hero-slim" data-role="donatur">
        <div class="portal-hero-copy">
            <h1>Data akun Anda.</h1>
            <p>Perbarui identitas dan keamanan akun donatur AlamKu.</p>
        </div>
    </section>

    <div class="form-layout">
        <aside class="form-aside">
            <h2>{{ $user->name }}</h2>
            <p>
                Profil ini digunakan untuk menghubungkan adopsi, pembayaran, dan
                kabar pohon yang Anda dukung.
            </p>
            <div class="aside-note">
                Email harus unik. Biarkan kata sandi kosong jika tidak ingin mengubahnya.
                Perubahan langsung berlaku setelah disimpan.
            </div>
        </aside>

        <div class="form-card">
            <form method="POST" action="{{ route('akun.profile.update') }}">
                @csrf
                @method('PUT')

                <div class="form-grid">
                    <div class="form-group full">
                        <label for="name">Nama Lengkap <span class="req">*</span></label>
                        <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}">
                        @error('name')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">Alamat Email <span class="req">*</span></label>
                        <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}">
                        @error('email')
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
                    <a class="btn" href="{{ route('akun.dashboard') }}">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
