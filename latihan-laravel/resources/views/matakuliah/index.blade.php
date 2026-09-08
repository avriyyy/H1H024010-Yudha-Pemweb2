@extends('layouts.app')
@section('judul', 'Daftar Matakuliah')
@section('konten')
<x-kartu-info judul="Informasi">
Data pada halaman ini masih berupa array statis. Pada modul
berikutnya data akan diambil dari basis data.
</x-kartu-info>
<h1 class="h3 mb-4">Daftar Matakuliah</h1>
<form action="{{ route('matakuliah.index') }}" method="GET"
class="row g-2 mb-3">
<div class="col-auto">
<input type="text" name="q" value="{{ $kataKunci }}"
class="form-control" placeholder="Cari kode / nama matakuliah...">
</div>
<div class="col-auto">
<button type="submit" class="btn btn-primary">Cari</button>
</div>
@if ($kataKunci !== '')
<div class="col-auto">
<a href="{{ route('matakuliah.index') }}" class="btn btn-outline-secondary">
Reset
</a>
</div>
@endif
</form>
@if ($kataKunci !== '')
<p>Hasil pencarian untuk "<strong>{{ $kataKunci }}</strong>":
{{ count($daftarMatakuliah) }} matakuliah.</p>
@endif
<table class="table table-bordered bg-white">
<thead>
<tr>
<th>Kode</th>
<th>Nama</th>
<th>SKS</th>
<th>Aksi</th>
</tr>
</thead>
<tbody>
@forelse ($daftarMatakuliah as $matakuliah)
<tr>
<td>{{ $matakuliah['kode'] }}</td>
<td>{{ $matakuliah['nama'] }}</td>
<td><x-badge-sks :sks="$matakuliah['sks']" /></td>
<td>
<a href="{{ route('matakuliah.show',
$matakuliah['kode']) }}" class="btn btn-sm btn-primary">
Detail
</a>
</td>
</tr>
@empty
<tr>
<td colspan="4">Data belum tersedia</td>
</tr>
@endforelse
</tbody>
</table>
@endsection
