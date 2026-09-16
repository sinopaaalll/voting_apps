@extends('layouts.app')

@section('title', 'Data Employee — SuaraKita')

@section('content')
<div class="page-header employee-page-header">
    <div><span class="eyebrow">Data pemilih</span><h1>Employee</h1><p>Kelola employee yang berhak memberikan suara.</p></div>
    <div class="header-actions">
        <button class="btn btn-ghost" type="button" onclick="document.getElementById('import-dialog').showModal()">Import Excel</button>
        <a class="btn btn-primary" href="{{ route('admin.employees.create') }}">+ Tambah employee</a>
    </div>
</div>

<dialog class="import-dialog" id="import-dialog">
    <form method="dialog"><button class="dialog-close" aria-label="Tutup">×</button></form>
    <span class="import-icon">XLS</span>
    <h2>Import data employee</h2>
    <p>Unggah file XLSX, XLS, atau CSV tanpa batas jumlah baris dari aplikasi. NIK yang sudah terdaftar akan diperbarui.</p>
    <a class="template-link" href="{{ route('admin.employees.template') }}">Unduh template Excel</a>
    <form method="POST" enctype="multipart/form-data" action="{{ route('admin.employees.import') }}" class="form-stack" data-import-form>
        @csrf
        <label class="upload-field">
            <span>Pilih file</span>
            <input type="file" name="file" accept=".xlsx,.xls,.csv" required>
            <small>Header: nik, name, department, employment_status, position</small>
        </label>
        <button class="btn btn-primary btn-block" type="submit" data-import-button>Import employee</button>
        <div class="import-loading" data-import-loading hidden role="status" aria-live="polite"><span class="loading-spinner"></span><div><strong>Sedang memproses data</strong><small>Jangan tutup halaman ini.</small></div></div>
    </form>
</dialog>

<form class="filter-bar" method="GET" action="{{ route('admin.employees.index') }}" data-employee-filter-form>
    <input name="q" value="{{ request('q') }}" placeholder="Cari NIK atau nama..." aria-label="Cari NIK atau nama" autocomplete="off" data-employee-search>
    <select class="js-department-select" name="department" aria-label="Filter department" data-placeholder="Cari department...">
        <option value="">Semua department</option>
        @foreach ($departments as $department)
            <option value="{{ $department }}" @selected(request('department') === $department)>{{ $department }}</option>
        @endforeach
    </select>
    <select class="js-voting-filter-select" name="voting_status" aria-label="Filter status voting">
        <option value="">Semua status voting</option>
        <option value="voted" @selected(request('voting_status') === 'voted')>Sudah memilih</option>
        <option value="not_voted" @selected(request('voting_status') === 'not_voted')>Belum memilih</option>
    </select>
    <button class="btn btn-dark" type="submit">Terapkan</button>
    <a class="btn btn-ghost" href="{{ route('admin.employees.index') }}" data-filter-reset @if (! request()->hasAny(['q', 'department', 'voting_status'])) hidden @endif>Reset</a>
</form>

<section class="employee-results" data-employee-results aria-live="polite">
<form id="bulk-delete-form" method="POST" action="{{ route('admin.employees.bulk-destroy') }}" data-confirm-delete data-confirm-title="Hapus employee yang dipilih?" data-confirm-text="Employee yang sudah voting akan dilewati. Data lain yang dihapus tidak dapat dipulihkan." data-confirm-button="Ya, hapus pilihan">
    @csrf @method('DELETE')
</form>

<div class="bulk-toolbar" data-bulk-toolbar hidden>
    <strong><span data-selected-count>0</span> employee dipilih</strong>
    <button class="btn btn-small btn-danger" type="submit" form="bulk-delete-form" data-bulk-delete disabled>Hapus pilihan</button>
</div>

<div class="table-wrap employee-table-wrap">
    <table class="employee-table">
        <thead>
            <tr>
                <th class="checkbox-cell"><input type="checkbox" data-select-all aria-label="Pilih semua employee yang dapat dihapus"></th>
                <th>Employee</th><th>Department</th><th>Status</th><th>Voting</th><th class="align-right">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($employees as $employee)
                <tr>
                    <td class="checkbox-cell" data-label="Pilih">
                        <input type="checkbox" name="employee_ids[]" value="{{ $employee->id }}" form="bulk-delete-form" data-row-checkbox aria-label="Pilih {{ $employee->name }}" @disabled($employee->voting_exists)>
                    </td>
                    <td data-label="Employee"><strong>{{ $employee->name }}</strong><small>{{ $employee->nik }} · {{ $employee->position }}</small></td>
                    <td data-label="Department">{{ $employee->department }}</td>
                    <td data-label="Status"><span class="badge badge-neutral">{{ ucfirst($employee->employment_status) }}</span></td>
                    <td data-label="Voting"><span class="badge {{ $employee->voting_exists ? 'badge-success' : 'badge-warning' }}">{{ $employee->voting_exists ? 'Sudah memilih' : 'Belum memilih' }}</span></td>
                    <td class="actions" data-label="Aksi">
                        <a class="btn btn-small btn-ghost" href="{{ route('admin.employees.edit', $employee) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.employees.destroy', $employee) }}" data-confirm-delete data-confirm-title="Hapus {{ $employee->name }}?" data-confirm-text="Data employee yang dihapus tidak dapat dipulihkan.">
                            @csrf @method('DELETE')
                            <button class="btn btn-small btn-danger" type="submit" @disabled($employee->voting_exists)>Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="empty-state"><strong>Data employee belum tersedia</strong><p>Tambah manual atau import file Excel untuk memulai.</p></div></td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="pagination-wrap">{{ $employees->links() }}</div>
</section>
@endsection
