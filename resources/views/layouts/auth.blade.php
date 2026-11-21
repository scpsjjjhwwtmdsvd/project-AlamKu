<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Masuk') &middot; AlamKu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/alamku.css') }}">
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
    @stack('head')
</head>
<body class="auth-body">
    <header class="site-header">
        <div class="header-inner">
            <a class="logo" href="{{ route('home') }}" aria-label="AlamKu — beranda">
                <span class="logo-mark" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <path d="M12 21c0-6 1.5-11 8-15-1 8-4 12-8 15Z" fill="currentColor" opacity=".9"/>
                        <path d="M12 21C12 15 10.5 10 4 6c1 8 4 12 8 15Z" fill="currentColor" opacity=".55"/>
                    </svg>
                </span>
                <span class="logo-text">AlamKu<small>Conservation</small></span>
            </a>

            <div class="header-actions">
                <a class="btn btn-ghost" href="{{ route('home') }}">Kembali ke beranda</a>
                <a class="btn btn-dark" href="{{ route('program.index') }}">Lihat Program</a>
            </div>
        </div>
    </header>

    @if (session('success'))
        <div class="auth-flash alert success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="auth-flash alert error">{{ session('error') }}</div>
    @endif

    @yield('content')

    @stack('scripts')
</body>
</html>
