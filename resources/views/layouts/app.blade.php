@php
    $currentRoute = request()->route()?->getName();
    $currentPrefix = $currentRoute ? explode('.', $currentRoute)[0] : null;
    $canCreate = $currentPrefix && \Illuminate\Support\Facades\Route::has($currentPrefix.'.create');
    $currentUser = auth()->user();
    $initials = collect(explode(' ', (string) $currentUser?->name))
        ->filter()
        ->take(2)
        ->map(fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');

    $primaryNavigation = [
        'proyek' => 'Proyek',
        'pohon' => 'Pohon',
        'pesanan-adopsi' => 'Adopsi',
        'laporan-lapangan' => 'Laporan',
    ];

    $masterNavigation = [
        'kelompok-tani' => 'Kelompok Tani',
        'lokasi-hutan' => 'Lokasi Hutan',
        'jenis-pohon' => 'Jenis Pohon',
        'alokasi-dana' => 'Alokasi Dana',
        'pembayaran' => 'Pembayaran',
        'foto-geotag' => 'Foto Geotag',
        'log-perubahan' => 'Log Perubahan',
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Konsol Admin') &middot; AlamKu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/alamku.css') }}">
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
    @stack('head')
</head>
<body class="portal" data-role="admin">
    <header class="site-header">
        <div class="header-inner">
            <a class="logo" href="{{ route('admin.dashboard') }}" aria-label="AlamKu: Konsol Admin">
                <span class="logo-mark" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <path d="M12 21c0-6 1.5-11 8-15-1 8-4 12-8 15Z" fill="currentColor" opacity=".9"/>
                        <path d="M12 21C12 15 10.5 10 4 6c1 8 4 12 8 15Z" fill="currentColor" opacity=".55"/>
                    </svg>
                </span>
                <span class="logo-text">AlamKu<small>Conservation</small></span>
            </a>

            <nav class="nav-links" aria-label="Navigasi data">
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dasbor</a>
                @foreach ($primaryNavigation as $route => $label)
                    <a href="{{ route($route.'.index') }}" class="{{ request()->routeIs($route.'.*') ? 'active' : '' }}">{{ $label }}</a>
                @endforeach

                <details class="nav-drop">
                    <summary>
                        Data Master
                        <svg width="10" height="6" viewBox="0 0 10 6" fill="none" aria-hidden="true">
                            <path d="m1 1 4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </summary>
                    <div class="nav-drop-panel">
                        @foreach ($masterNavigation as $route => $label)
                            <a href="{{ route($route.'.index') }}" class="{{ request()->routeIs($route.'.*') ? 'active' : '' }}">{{ $label }}</a>
                        @endforeach
                    </div>
                </details>
            </nav>

            <div class="header-actions">
                <a class="btn btn-ghost" href="{{ route('home') }}">Situs</a>
                @if ($canCreate)
                    <a class="btn btn-dark" href="{{ route($currentPrefix.'.create') }}">+ Tambah Data</a>
                @endif
                <details class="user-menu">
                    <summary class="user-chip">
                        <span class="avatar">{{ $initials }}</span>
                        <span class="user-chip-name">{{ $currentUser?->name }}</span>
                    </summary>
                    <div class="user-panel">
                        <div class="user-panel-head">
                            <strong>{{ $currentUser?->name }}</strong>
                            <span>{{ $currentUser?->email }}</span>
                        </div>
                        <a href="{{ route('admin.dashboard') }}">Dasbor admin</a>
                        <a href="{{ route('proyek.index') }}">Kelola proyek</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit">Keluar</button>
                        </form>
                    </div>
                </details>
            </div>
        </div>
    </header>

    <div class="role-bar role-bar-admin">
        <div class="role-bar-inner">
            <span class="role-bar-label"><span class="role-dot"></span>Konsol Admin</span>
            <span class="role-bar-tagline">Kelola seluruh data konservasi, donasi, dan verifikasi lapangan.</span>
            <span class="role-bar-date">{{ now()->translatedFormat('d F Y') }}</span>
        </div>
    </div>

    <main class="admin-main">
        @if (session('success'))
            <div class="alert success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert error">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>

    <footer class="site-footer" style="margin-top:0">
        <div><strong>AlamKu</strong> &middot; Konsol Admin</div>
        <div class="footer-links">
            <a href="{{ route('home') }}">Beranda</a>
            <a href="{{ route('admin.dashboard') }}">Dasbor</a>
            <a href="{{ route('laporan-lapangan.index') }}">Laporan</a>
        </div>
        <div>&copy; {{ date('Y') }} AlamKu. Tumbuh bersama.</div>
    </footer>

    <script src="{{ asset('js/user-menu.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
