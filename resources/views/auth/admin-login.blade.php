@extends('layouts.app')

@section('title', 'Login Admin — SuaraKita')

@section('content')
<section class="auth-grid auth-grid-admin">
    <div class="auth-intro">
        <span class="eyebrow">Area pengelola</span>
        <h1>Kelola pemilihan dari satu tempat.</h1>
        <p>Atur daftar employee dan kandidat, pantau partisipasi, lalu lihat perolehan suara secara agregat.</p>
    </div>

    <div class="auth-card">
        <div class="card-heading">
            <span class="step-number">A</span>
            <div>
                <h2>Login admin</h2>
                <p>Masukkan kredensial administrator.</p>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.login.store') }}" class="form-stack">
            @csrf
            <label class="field">
                <span>Username</span>
                <input name="username" value="{{ old('username') }}" autocomplete="username" autofocus required>
            </label>
            <label class="field">
                <span>Password</span>
                <input type="password" name="password" autocomplete="current-password" required>
            </label>
            <button class="btn btn-dark btn-block" type="submit">Masuk ke dashboard <span>→</span></button>
        </form>
        <a class="subtle-link" href="{{ route('employee.login') }}">Kembali ke halaman pemilih</a>
    </div>
</section>
@endsection
