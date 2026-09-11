@extends('layouts.app')

@section('title', 'Dashboard Admin — SuaraKita')

@section('content')
<div class="page-header">
    <div>
        <span class="eyebrow">Dashboard admin</span>
        <h1>Ringkasan pemilihan</h1>
        <p>Pantau kesiapan data dan partisipasi employee secara langsung.</p>
    </div>
    <a class="btn btn-primary" href="{{ route('admin.results') }}">Lihat hasil lengkap</a>
</div>

<section class="stat-grid">
    <article class="stat-card accent-blue"><span>Total employee</span><strong>{{ number_format($totalEmployee) }}</strong><small>Pemilih terdaftar</small></article>
    <article class="stat-card accent-green"><span>Sudah memilih</span><strong>{{ number_format($totalVotes) }}</strong><small>{{ $participation }}% partisipasi</small></article>
    <article class="stat-card accent-orange"><span>Belum memilih</span><strong>{{ number_format($notVoted) }}</strong><small>Employee tersisa</small></article>
    <article class="stat-card accent-purple"><span>Kandidat</span><strong>{{ number_format($kandidat->count()) }}</strong><small>Peserta pemilihan</small></article>
</section>

<section class="panel">
    <div class="panel-head">
        <div><h2>Progres partisipasi</h2><p>Perbandingan employee yang sudah memberikan suara.</p></div>
        <strong>{{ $participation }}%</strong>
    </div>
    <div class="progress-track"><span style="width: {{ min(100, $participation) }}%"></span></div>
</section>

<section class="quick-grid">
    <a class="quick-card" href="{{ route('admin.employees.index') }}"><span class="quick-icon">E</span><div><h3>Kelola employee</h3><p>Tambah dan perbarui daftar pemilih.</p></div><b>→</b></a>
    <a class="quick-card" href="{{ route('admin.kandidat.index') }}"><span class="quick-icon">K</span><div><h3>Kelola kandidat</h3><p>Atur profil dan nomor urut.</p></div><b>→</b></a>
</section>
@endsection
