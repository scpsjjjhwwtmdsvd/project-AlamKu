@php
    $role = $role ?? 'donatur';

    $roleMeta = [
        'donatur' => [
            'label' => 'Ruang Donatur',
            'short' => 'Ruang Donatur',
            'tagline' => 'Pantau adopsi, pohon, dan dampak kontribusi Anda.',
            'home' => 'akun.dashboard',
            'profile' => 'akun.profile',
            'nav' => [
                ['route' => 'akun.dashboard', 'label' => 'Ringkasan', 'active' => 'akun.dashboard'],
                ['route' => 'akun.adoptions', 'label' => 'Adopsi Saya', 'active' => 'akun.adoptions*'],
                ['route' => 'program.index', 'label' => 'Jelajahi Program', 'active' => 'program.*'],
                ['route' => 'akun.profile', 'label' => 'Profil', 'active' => 'akun.profile*'],
            ],
        ],
        'mitra' => [
            'label' => 'Ruang Tani',
            'short' => 'Ruang Tani',
            'tagline' => 'Kelola proyek, pohon, dan laporan lapangan kelompok Anda.',
            'home' => 'mitra.dashboard',
            'profile' => 'mitra.profile',
            'nav' => [
                ['route' => 'mitra.dashboard', 'label' => 'Ringkasan', 'active' => 'mitra.dashboard'],
                ['route' => 'mitra.projects', 'label' => 'Proyek', 'active' => 'mitra.projects'],
                ['route' => 'mitra.trees', 'label' => 'Pohon', 'active' => 'mitra.trees'],
                ['route' => 'mitra.reports', 'label' => 'Laporan', 'active' => 'mitra.reports*'],
                ['route' => 'mitra.profile', 'label' => 'Profil', 'active' => 'mitra.profile*'],
            ],
        ],
    ];

    $meta = $roleMeta[$role];
    $currentUser = auth()->user();
    $initials = collect(explode(' ', (string) $currentUser?->name))
        ->filter()
        ->take(2)
        ->map(fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $meta['label']) &middot; AlamKu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/alamku.css') }}">
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
    <link rel="stylesheet" href="{{ asset($role === 'mitra' ? 'css/portal-mitra.css' : 'css/portal-donatur.css') }}">
    @stack('head')
</head>
<body class="portal" data-role="{{ $role }}">
    <header class="site-header">
        <div class="header-inner">
            <a class="logo" href="{{ route($meta['home']) }}" aria-label="AlamKu — {{ $meta['label'] }}">
                <span class="logo-mark" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <path d="M12 21c0-6 1.5-11 8-15-1 8-4 12-8 15Z" fill="currentColor" opacity=".9"/>
                        <path d="M12 21C12 15 10.5 10 4 6c1 8 4 12 8 15Z" fill="currentColor" opacity=".55"/>
                    </svg>
                </span>
                <span class="logo-text">AlamKu<small>Conservation</small></span>
            </a>

            <nav class="nav-links" aria-label="Navigasi {{ $meta['label'] }}">
                @foreach ($meta['nav'] as $item)
                    <a href="{{ route($item['route']) }}" class="{{ request()->routeIs($item['active']) ? 'active' : '' }}">{{ $item['label'] }}</a>
                @endforeach
            </nav>

            <div class="header-actions">
                <a class="btn btn-ghost" href="{{ route('home') }}">Situs</a>
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
                        <a href="{{ route($meta['home']) }}">Dasbor</a>
                        <a href="{{ route($meta['profile']) }}">Profil</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit">Keluar</button>
                        </form>
                    </div>
                </details>
            </div>
        </div>
    </header>

    <div class="role-bar role-bar-{{ $role }}">
        <div class="role-bar-inner">
            <span class="role-bar-label"><span class="role-dot"></span>{{ $meta['label'] }}</span>
            <span class="role-bar-tagline">{{ $meta['tagline'] }}</span>
            <span class="role-bar-date">{{ now()->translatedFormat('d F Y') }}</span>
        </div>
    </div>

    <main class="portal-main">
        @if (session('success'))
            <div class="alert success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert error">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>

    <footer class="site-footer">
        <div><strong>AlamKu</strong> &middot; {{ $meta['label'] }}</div>
        <div class="footer-links">
            <a href="{{ route('home') }}">Beranda</a>
            <a href="{{ route('program.index') }}">Program</a>
            <a href="{{ route($meta['profile']) }}">Profil</a>
        </div>
        <div>&copy; {{ date('Y') }} AlamKu. Tumbuh bersama.</div>
    </footer>

    @stack('scripts')
</body>
</html>
