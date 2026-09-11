@extends('layouts.app')

@section('title', 'Tambah Employee — SuaraKita')

@section('content')
<div class="page-header"><div><span class="eyebrow">Data pemilih</span><h1>Tambah employee</h1><p>Daftarkan employee agar dapat masuk menggunakan NIK.</p></div></div>
<section class="form-panel"><form method="POST" action="{{ route('admin.employees.store') }}" class="form-stack">@csrf @include('admin.employees._form', ['submitLabel' => 'Simpan data'])</form></section>
@endsection
