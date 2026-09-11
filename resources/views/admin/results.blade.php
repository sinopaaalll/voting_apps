@extends('layouts.app')

@section('title', 'Hasil Voting — SuaraKita')

@section('content')
<div class="page-header">
    <div><span class="eyebrow">Hasil agregat</span><h1>Perolehan suara</h1><p>Hasil tidak menampilkan pilihan individu employee.</p></div>
    <span class="badge badge-success">{{ $totalVotes }} dari {{ $totalEmployee }} suara masuk</span>
</div>
<section class="results-grid">
    @forelse ($kandidat as $item)
        @php($percentage = $totalVotes > 0 ? round(($item->votes_count / $totalVotes) * 100, 1) : 0)
        <article class="result-card">
            <span class="result-rank">{{ str_pad($item->nomor_urut, 2, '0', STR_PAD_LEFT) }}</span>
            <div class="result-content">
                <h2>{{ $item->name }}</h2><p>Kandidat nomor {{ $item->nomor_urut }}</p>
                <div class="result-count"><strong>{{ $item->votes_count }}</strong><span>suara</span></div>
                <div class="result-bar"><span style="width: {{ $percentage }}%"></span></div>
                <div class="result-meta"><span>Persentase perolehan</span><strong>{{ $percentage }}%</strong></div>
            </div>
        </article>
    @empty
        <div class="empty-state span-all"><strong>Belum ada kandidat</strong><p>Tambahkan kandidat untuk mulai menampilkan hasil.</p></div>
    @endforelse
</section>
@endsection
