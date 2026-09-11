@extends('layouts.app')

@section('title', 'Edit Employee — SuaraKita')

@section('content')
<div class="page-header"><div><span class="eyebrow">Data pemilih</span><h1>Edit employee</h1><p>Perbarui identitas dan informasi pekerjaan employee.</p></div></div>
<section class="form-panel"><form method="POST" action="{{ route('admin.employees.update', $employee) }}" class="form-stack">@csrf @method('PUT') @include('admin.employees._form', ['submitLabel' => 'Simpan perubahan'])</form></section>
@endsection
