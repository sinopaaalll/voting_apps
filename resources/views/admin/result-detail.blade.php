@extends('layouts.app')

@section('title', 'Pemilih ' . $kandidat->name . ' — SuaraKita')

@section('content')
<div class="page-header">
    <div>
        <span class="eyebrow">Detail hasil kandidat</span>
        <h1>{{ $kandidat->name }}</h1>
        <p>Employee yang memilih kandidat nomor {{ $kandidat->nomor_urut }}.</p>
    </div>
    <div class="header-actions">
        <a class="btn btn-ghost" href="{{ route('admin.results') }}">Kembali</a>
        <a class="btn btn-primary" href="{{ route('admin.results.export') }}">Export Excel</a>
    </div>
</div>

<section class="candidate-result-summary">
    <span class="result-rank">{{ str_pad($kandidat->nomor_urut, 2, '0', STR_PAD_LEFT) }}</span>
    <div><small>Total perolehan</small><strong>{{ $votes->total() }} suara</strong></div>
</section>

<section class="voter-detail-grid">
    @forelse ($votes as $vote)
        <article class="voter-detail-card">
            <div class="voter-detail-head">
                <span>{{ mb_strtoupper(mb_substr($vote->employee->name, 0, 1)) }}</span>
                <div><strong>{{ $vote->employee->name }}</strong><small>{{ $vote->employee->nik }}</small></div>
            </div>
            <dl>
                <div><dt>Department</dt><dd>{{ $vote->employee->department }}</dd></div>
                <div><dt>Status</dt><dd>{{ ucfirst($vote->employee->employment_status) }}</dd></div>
                <div><dt>Jabatan</dt><dd>{{ $vote->employee->position }}</dd></div>
                <div><dt>Waktu memilih</dt><dd>{{ $vote->created_at->format('d M Y, H:i') }}</dd></div>
            </dl>
        </article>
    @empty
        <div class="empty-state span-all"><strong>Belum ada suara</strong><p>Belum ada employee yang memilih kandidat ini.</p></div>
    @endforelse
</section>

<div class="pagination-wrap">{{ $votes->links() }}</div>
@endsection
