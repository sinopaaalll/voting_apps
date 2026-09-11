@extends('layouts.app')

@section('title', 'Data Kandidat — SuaraKita')

@section('content')
<div class="page-header">
    <div><span class="eyebrow">Peserta pemilihan</span><h1>Kandidat</h1><p>Kelola profil kandidat sebelum voting dimulai.</p></div>
    <a class="btn btn-primary" href="{{ route('admin.kandidat.create') }}">+ Tambah kandidat</a>
</div>

<section class="admin-candidate-grid">
    @forelse ($kandidat as $item)
        <article class="admin-candidate-card">
            <div class="candidate-photo-wrap"><img src="{{ Storage::url($item->photo) }}" alt="Foto {{ $item->name }}"><span class="floating-number">{{ str_pad($item->nomor_urut, 2, '0', STR_PAD_LEFT) }}</span></div>
            <div class="candidate-body"><h2>{{ $item->name }}</h2><p>{{ Str::limit($item->visi_misi, 115) }}</p><span class="badge {{ $item->votes_count ? 'badge-success' : 'badge-neutral' }}">{{ $item->votes_count }} suara</span></div>
            <div class="candidate-actions">
                @if ($item->votes_count === 0)<a class="btn btn-small btn-ghost" href="{{ route('admin.kandidat.edit', $item) }}">Edit</a>@endif
                <form method="POST" action="{{ route('admin.kandidat.destroy', $item) }}" onsubmit="return confirm('Hapus kandidat ini?')">@csrf @method('DELETE')<button class="btn btn-small btn-danger" type="submit" @disabled($item->votes_count > 0)>Hapus</button></form>
            </div>
        </article>
    @empty
        <div class="empty-state span-all"><strong>Belum ada kandidat</strong><p>Tambahkan kandidat pertama beserta foto dan visi-misinya.</p></div>
    @endforelse
</section>
@endsection
