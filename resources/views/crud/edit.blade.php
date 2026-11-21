@extends('layouts.app')

@section('title', $heading)

@section('content')
    <section class="admin-hero slim" data-module="{{ $routePrefix }}">
        <div>
            <h1>{{ $heading }}</h1>
            <p>Perbarui informasi {{ strtolower($title) }} #{{ $item->getKey() }}. Perubahan langsung tersimpan setelah form dikirim.</p>
        </div>
        <a class="btn btn-ghost-light" href="{{ route($routePrefix.'.show', $item->getKey()) }}">Lihat Detail</a>
    </section>

    <div class="form-layout">
        <aside class="form-aside">
            <h2>{{ $title }} #{{ $item->getKey() }}</h2>
            <p>
                Anda sedang mengubah data yang sudah tersimpan. Nilai lama tetap
                dapat dilihat pada halaman detail sebelum perubahan disimpan.
            </p>
            <div class="aside-note">
                Jika hanya ingin melihat data tanpa mengubah apa pun, gunakan
                tombol Lihat Detail atau Kembali.
            </div>
        </aside>

        <div class="form-card">
            <form method="POST" action="{{ route($routePrefix.'.update', $item->getKey()) }}">
                @csrf
                @method('PUT')

                @include('crud._form', ['fields' => $fields, 'values' => $values])

                <div class="form-actions">
                    <button class="btn btn-dark" type="submit">Simpan Perubahan</button>
                    <a class="btn" href="{{ route($routePrefix.'.show', $item->getKey()) }}">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
