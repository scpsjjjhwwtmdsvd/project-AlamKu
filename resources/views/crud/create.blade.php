@extends('layouts.app')

@section('title', $heading)

@section('content')
    <section class="admin-hero slim" data-module="{{ $routePrefix }}">
        <div>
            <h1>{{ $heading }}</h1>
            <p>Isi data {{ strtolower($title) }} dengan lengkap. Kolom bertanda bintang wajib diisi.</p>
        </div>
        <a class="btn btn-ghost-light" href="{{ route($routePrefix.'.index') }}">&larr; Kembali</a>
    </section>

    <div class="form-layout">
        <aside class="form-aside">
            <h2>{{ $title }}</h2>
            <p>
                Pastikan relasi dan nilai numerik sudah sesuai. Data yang tersimpan
                akan langsung muncul di daftar {{ strtolower($title) }}.
            </p>
            <div class="aside-note">
                Tips: periksa kembali tanggal, koordinat, dan relasi sebelum menyimpan.
                Gunakan tombol Batal untuk kembali tanpa menyimpan perubahan.
            </div>
        </aside>

        <div class="form-card">
            <form method="POST" action="{{ route($routePrefix.'.store') }}">
                @csrf

                @include('crud._form', ['fields' => $fields, 'values' => $values])

                <div class="form-actions">
                    <button class="btn btn-dark" type="submit">Simpan Data</button>
                    <a class="btn" href="{{ route($routePrefix.'.index') }}">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
