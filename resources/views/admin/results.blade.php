@extends('layouts.app')

@section('title', 'Hasil Voting — SuaraKita')

@section('content')
<div class="page-header">
    <div><span class="eyebrow">Hasil pemilihan</span><h1>Perolehan suara</h1><p>Klik kartu kandidat untuk melihat employee yang memberikan suara.</p></div>
    <div class="header-actions">
        <span class="badge badge-success">{{ $totalVotes }} dari {{ $totalEmployee }} suara masuk</span>
        <a class="btn btn-primary" href="{{ route('admin.results.export') }}">Export Semua Kandidat</a>
    </div>
</div>
<section class="results-grid">
    @forelse ($kandidat as $item)
        @php($percentage = $totalVotes > 0 ? round(($item->votes_count / $totalVotes) * 100, 1) : 0)
        <a class="result-card result-card-link" href="{{ route('admin.results.show', $item) }}" aria-label="Lihat employee yang memilih {{ $item->name }}">
            <span class="result-rank">{{ str_pad($item->nomor_urut, 2, '0', STR_PAD_LEFT) }}</span>
            <div class="result-content">
                <h2>{{ $item->name }}</h2><p>Kandidat nomor {{ $item->nomor_urut }}</p>
                <div class="result-count"><strong>{{ $item->votes_count }}</strong><span>suara</span></div>
                <div class="result-bar"><span style="width: {{ $percentage }}%"></span></div>
                <div class="result-meta"><span>Persentase perolehan</span><strong>{{ $percentage }}%</strong></div>
                <span class="result-open">Lihat daftar pemilih <span aria-hidden="true">→</span></span>
            </div>
        </a>
    @empty
        <div class="empty-state span-all"><strong>Belum ada kandidat</strong><p>Tambahkan kandidat untuk mulai menampilkan hasil.</p></div>
    @endforelse
</section>
@endsection
