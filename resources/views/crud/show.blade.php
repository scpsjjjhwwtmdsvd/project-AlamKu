@extends('layouts.app')

@section('title', $title.' #'.$item->getKey())

@section('content')
    <section class="admin-hero slim" data-module="{{ $routePrefix }}">
        <div>
            <h1>{{ $title }} #{{ $item->getKey() }}</h1>
            <p>Semua informasi {{ strtolower($title) }} ini tersimpan dalam satu catatan yang saling terhubung.</p>
            <div class="admin-hero-meta">
                <span class="admin-pill">ID {{ $item->getKey() }}</span>
                <span class="admin-pill">{{ $title }}</span>
            </div>
        </div>
        <div class="hero-side-actions">
            <a class="btn btn-light" href="{{ route($routePrefix.'.edit', $item->getKey()) }}">Edit Data</a>
            <a class="btn btn-ghost-light" href="{{ route($routePrefix.'.index') }}">&larr; Kembali</a>
        </div>
    </section>

    <div class="detail-layout">
        <div class="data-card">
            <table class="detail-table">
                <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            <th>{{ $row['label'] }}</th>
                            <td>{{ $row['value'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <aside class="detail-side">
            <h3 style="margin-top:16px">{{ $title }}</h3>
            <p>
                Catatan ini merupakan bagian dari modul {{ strtolower($title) }}.
                Anda dapat memperbarui atau menghapusnya dari panel ini.
            </p>

            <div class="detail-actions">
                <a class="btn btn-dark" href="{{ route($routePrefix.'.edit', $item->getKey()) }}">Edit Data</a>
                <a class="btn" href="{{ route($routePrefix.'.index') }}">Kembali ke Daftar</a>
                <form method="POST" action="{{ route($routePrefix.'.destroy', $item->getKey()) }}" onsubmit="return confirm('Hapus data ini?');">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger" type="submit">Hapus Data</button>
                </form>
            </div>
        </aside>
    </div>
@endsection
