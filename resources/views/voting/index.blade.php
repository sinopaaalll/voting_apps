@extends('layouts.app')

@section('title', 'Pilih Kandidat — SuaraKita')

@section('content')
@if ($hasVoted)
    <section class="success-screen mobile-success-screen">
        <div class="success-mark" aria-hidden="true">✓</div>
        <span class="eyebrow">Suara tercatat</span>
        <h1>Terima kasih, {{ $employee->name }}.</h1>
        <p>Pilihan Anda telah disimpan dan tidak dapat diubah. Hasil pemilihan hanya dapat dilihat oleh admin.</p>
        <div class="success-detail"><span>NIK</span><strong>{{ $employee->nik }}</strong><span>Status</span><strong>Selesai memilih</strong></div>
        <form method="POST" action="{{ route('employee.logout') }}">@csrf<button class="btn btn-dark btn-block mobile-primary-action" type="submit">Keluar dengan aman</button></form>
    </section>
@else
    <div class="mobile-step voting-mobile-step"><span>Langkah 2 dari 2</span><strong>Pilih kandidat</strong></div>
    <div class="voting-hero">
        <div>
            <span class="eyebrow">Bilik suara digital</span>
            <h1>Pilih kandidat Anda</h1>
            <p>Geser ke bawah untuk melihat seluruh kandidat sebelum menentukan pilihan.</p>
        </div>
        <div class="voter-chip"><span>{{ mb_strtoupper(mb_substr($employee->name, 0, 1)) }}</span><div><small>Pemilih</small><strong>{{ $employee->name }}</strong><small>{{ $employee->nik }} · {{ $employee->department }}</small></div></div>
    </div>

    <div class="notice"><strong>Pilihan bersifat final.</strong> Periksa kembali sebelum menekan tombol kirim suara.</div>

    <section class="voting-grid">
        @forelse ($kandidat as $item)
            <article class="vote-card">
                <div class="vote-photo"><img src="{{ Storage::url($item->photo) }}" alt="Foto {{ $item->name }}"><span>{{ str_pad($item->nomor_urut, 2, '0', STR_PAD_LEFT) }}</span></div>
                <div class="vote-body">
                    <small>Kandidat nomor {{ $item->nomor_urut }}</small>
                    <h2>{{ $item->name }}</h2>
                    <button class="btn btn-primary btn-block mobile-primary-action" type="button" onclick="document.getElementById('confirm-{{ $item->id }}').showModal()">Pilih kandidat ini</button>
                </div>
            </article>

            <dialog class="vote-dialog" id="confirm-{{ $item->id }}">
                <form method="dialog"><button class="dialog-close" aria-label="Tutup">×</button></form>
                <span class="dialog-number">{{ str_pad($item->nomor_urut, 2, '0', STR_PAD_LEFT) }}</span>
                <h2>Konfirmasi pilihan</h2>
                <p>Anda akan memilih <strong>{{ $item->name }}</strong>. Pilihan ini tidak dapat diubah setelah dikirim.</p>
                <form method="POST" action="{{ route('voting.store') }}" class="dialog-actions">
                    @csrf
                    <input type="hidden" name="kandidat_id" value="{{ $item->id }}">
                    <input type="hidden" name="confirmation" value="1">
                    <button class="btn btn-primary mobile-primary-action" type="submit">Ya, kirim suara</button>
                    <button class="btn btn-ghost mobile-primary-action" type="button" onclick="document.getElementById('confirm-{{ $item->id }}').close()">Periksa lagi</button>
                </form>
            </dialog>
        @empty
            <div class="empty-state span-all"><strong>Kandidat belum tersedia</strong><p>Silakan hubungi admin pemilihan.</p></div>
        @endforelse
    </section>
@endif
@endsection
