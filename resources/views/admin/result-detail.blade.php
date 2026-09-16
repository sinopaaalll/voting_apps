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
        <a class="btn btn-primary" href="{{ route('admin.results.candidate-export', $kandidat) }}">Export Kandidat Ini</a>
    </div>
</div>

<section class="candidate-result-summary">
    <span class="result-rank">{{ str_pad($kandidat->nomor_urut, 2, '0', STR_PAD_LEFT) }}</span>
    <div><small>Total perolehan</small><strong>{{ $totalVotes }} suara</strong></div>
</section>

<form class="filter-bar result-detail-filter" method="GET" action="{{ route('admin.results.show', $kandidat) }}" data-candidate-result-filter-form>
    <input name="q" value="{{ request('q') }}" placeholder="Cari NIK atau nama employee..." aria-label="Cari NIK atau nama employee" autocomplete="off" data-candidate-result-search>
    <button class="btn btn-dark" type="submit">Cari</button>
    <a class="btn btn-ghost" href="{{ route('admin.results.show', $kandidat) }}" data-candidate-result-reset @if (! request()->filled('q')) hidden @endif>Reset</a>
</form>

<section class="candidate-result-list" data-candidate-result-list aria-live="polite">
    <div class="table-wrap employee-table-wrap">
        <table class="employee-table result-voter-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Waktu memilih</th>
                    <th class="align-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($votes as $vote)
                    <tr>
                        <td data-label="No">{{ $votes->firstItem() + $loop->index }}</td>
                        <td data-label="Employee"><strong>{{ $vote->employee->name }}</strong><small>{{ $vote->employee->nik }} · {{ $vote->employee->position }}</small></td>
                        <td data-label="Department">{{ $vote->employee->department }}</td>
                        <td data-label="Status"><span class="badge badge-neutral">{{ ucfirst($vote->employee->employment_status) }}</span></td>
                        <td data-label="Waktu memilih">{{ $vote->created_at->format('d M Y, H:i') }}</td>
                        <td class="actions" data-label="Aksi">
                            @if ($targetCandidates->isNotEmpty())
                                <form class="vote-move-form" method="POST" action="{{ route('admin.results.vote.move', $vote) }}" data-confirm-action data-confirm-title="Pindahkan suara {{ $vote->employee->name }}?" data-confirm-text="Suara akan dipindahkan ke :candidate. Perolehan kedua kandidat akan berubah." data-confirm-button="Ya, pindahkan">
                                    @csrf @method('PATCH')
                                    <select name="kandidat_id" aria-label="Kandidat tujuan untuk {{ $vote->employee->name }}" required>
                                        <option value="">Pilih kandidat tujuan</option>
                                        @foreach ($targetCandidates as $targetCandidate)
                                            <option value="{{ $targetCandidate->id }}">{{ str_pad($targetCandidate->nomor_urut, 2, '0', STR_PAD_LEFT) }} — {{ $targetCandidate->name }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-small btn-primary" type="submit">Pindahkan</button>
                                </form>
                            @else
                                <span class="badge badge-neutral">Tidak ada kandidat lain</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <strong>{{ request()->filled('q') ? 'Employee tidak ditemukan' : 'Belum ada suara' }}</strong>
                                <p>{{ request()->filled('q') ? 'Coba gunakan NIK atau nama yang berbeda.' : 'Belum ada employee yang memilih kandidat ini.' }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">{{ $votes->links() }}</div>
</section>
@endsection
