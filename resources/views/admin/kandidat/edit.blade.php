@extends('layouts.app')

@section('title', 'Edit Kandidat — SuaraKita')

@section('content')
<div class="page-header"><div><span class="eyebrow">Peserta pemilihan</span><h1>Edit kandidat</h1><p>Perubahan hanya diizinkan sebelum kandidat menerima suara.</p></div></div>
<section class="form-panel"><form method="POST" enctype="multipart/form-data" action="{{ route('admin.kandidat.update', $kandidat) }}" class="form-stack">@csrf @method('PUT') @include('admin.kandidat._form', ['submitLabel' => 'Simpan perubahan'])</form></section>
@endsection
