@extends('layouts.app')
@section('judul', 'Detail Matakuliah')
@section('konten')
<h1 class="h3 mb-4">Detail Matakuliah</h1>
<div class="card">
<div class="card-body">
<p class="mb-0">Kode matakuliah: <strong>{{ $kode }}</strong></p>
</div>
</div>
<a href="{{ route('matakuliah.index') }}" class="btn btn-secondary
mt-3">Kembali</a>
@endsection
