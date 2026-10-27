@extends('layouts.app')

@section('title', 'Masuk Pemilih — SuaraKita')

@section('content')
<section class="auth-grid voter-auth-grid">
    <div class="auth-intro voter-auth-intro">
        <span class="eyebrow">Pemilihan internal</span>
        <h1>Suara Anda menentukan langkah berikutnya.</h1>
        <p>Masuk menggunakan NIK dan konfirmasi tanggal lahir sebelum memberikan satu pilihan final Anda.</p>
        <div class="trust-row">
            <span><b>1</b> NIK untuk satu suara</span>
            <span><b>✓</b> Pilihan tercatat permanen</span>
        </div>
    </div>

    <div class="auth-card voter-auth-card">
        <div class="mobile-step"><span>Langkah 1 dari 2</span><strong>Masuk</strong></div>
        <div class="card-heading">
            <span class="step-number">01</span>
            <div><h2>Masuk sebagai pemilih</h2><p>Verifikasi NIK dan tanggal lahir Anda.</p></div>
        </div>
        <form method="POST" action="{{ route('employee.login.store') }}" class="form-stack">
            @csrf
            <label class="field">
                <span>NIK</span>
                <input type="text" name="nik" value="{{ old('nik') }}" maxlength="30" autocomplete="username" placeholder="xxxxxxx" autofocus required>
            </label>
            <label class="field">
                <span>Tanggal lahir</span>
                <input type="date" name="birth_date" value="{{ old('birth_date') }}" autocomplete="bday" required>
            </label>
            <button class="btn btn-primary btn-block mobile-primary-action" type="submit">Lanjutkan <span aria-hidden="true">→</span></button>
        </form>
        <p class="login-help">Pastikan NIK dan tanggal lahir sesuai data yang didaftarkan admin.</p>
        <a class="subtle-link" href="{{ route('admin.login') }}">Masuk sebagai admin</a>
    </div>
</section>
@endsection
