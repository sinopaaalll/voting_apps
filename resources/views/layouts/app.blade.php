<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SuaraKita')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="page-shell">
        <header class="topbar">
            <a class="brand" href="{{ session('admin_authenticated') ? route('admin.dashboard') : (session('employee_id') ? route('voting.index') : route('employee.login')) }}">
                <span class="brand-mark">S</span>
                <span>SuaraKita</span>
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
