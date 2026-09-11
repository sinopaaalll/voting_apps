@extends('layouts.app')

@section('title', 'Tambah Kandidat — SuaraKita')

@section('content')
<div class="page-header"><div><span class="eyebrow">Peserta pemilihan</span><h1>Tambah kandidat</h1><p>Lengkapi profil kandidat yang akan tampil pada halaman voting.</p></div></div>
<section class="form-panel"><form method="POST" enctype="multipart/form-data" action="{{ route('admin.kandidat.store') }}" class="form-stack">@csrf @include('admin.kandidat._form', ['submitLabel' => 'Simpan kandidat'])</form></section>
@endsection
