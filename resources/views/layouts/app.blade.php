<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', config('app.description'))">
    <meta name="application-name" content="{{ config('app.name') }}">
    <meta name="theme-color" content="#176b87">
    <meta name="color-scheme" content="light">
    <meta name="format-detection" content="telephone=no">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <meta name="robots" content="@yield('robots', 'noindex, nofollow, noarchive')">

    <meta property="og:type" content="website">
    <meta property="og:locale" content="id_ID">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:title" content="@yield('title', config('app.name'))">
    <meta property="og:description" content="@yield('meta_description', config('app.description'))">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('images/suarakita-share.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="{{ config('app.name') }} — aplikasi voting internal">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', config('app.name'))">
    <meta name="twitter:description" content="@yield('meta_description', config('app.description'))">
    <meta name="twitter:image" content="{{ asset('images/suarakita-share.png') }}">

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('images/suarakita-mark.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="page-shell">
        <header class="topbar">
            <a class="brand" href="{{ session('admin_authenticated') ? route('admin.dashboard') : (session('employee_id') ? route('voting.index') : route('employee.login')) }}">
                <img class="brand-mark" src="{{ asset('images/suarakita-mark.svg') }}" width="36" height="36" alt="">
                <span>{{ config('app.name') }}</span>
            </a>

            @if (session('admin_authenticated') && request()->routeIs('admin.*') && !request()->routeIs('admin.login*'))
                <nav class="nav-links" aria-label="Navigasi admin">
                    <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Ringkasan</a>
                    <a class="{{ request()->routeIs('admin.employees.*') ? 'active' : '' }}" href="{{ route('admin.employees.index') }}">Employee</a>
                    <a class="{{ request()->routeIs('admin.kandidat.*') ? 'active' : '' }}" href="{{ route('admin.kandidat.index') }}">Kandidat</a>
                    <a class="{{ request()->routeIs('admin.results*') ? 'active' : '' }}" href="{{ route('admin.results') }}">Hasil</a>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button class="nav-logout" type="submit">Keluar</button>
                    </form>
                </nav>
            @elseif (session('employee_id') && request()->routeIs('voting.*'))
                <form method="POST" action="{{ route('employee.logout') }}">
                    @csrf
                    <button class="nav-logout" type="submit">Keluar</button>
                </form>
            @endif
        </header>

        <main class="main-content">
            @if (session('success'))
                <div class="alert alert-success" role="status">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-error" role="alert">
                    <strong>Periksa kembali:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>

    </div>
</body>
</html>
