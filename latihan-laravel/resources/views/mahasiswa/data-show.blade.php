@extends('layouts.app')
@section('judul', 'Detail Mahasiswa')
@section('konten')
<h1 class="h3 mb-4">Detail Mahasiswa</h1>
<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title">{{ $mahasiswa->nama }}</h5>
        <table class="table table-sm table-borderless mb-0">
            <tr>
                <th class="w-25">NIM</th>
                <td>{{ $mahasiswa->nim }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $mahasiswa->email }}</td>
            </tr>
            <tr>
                <th>Program Studi</th>
                <td>{{ $mahasiswa->programStudi->nama }}</td>
            </tr>
            <tr>
                <th>Angkatan</th>
                <td>{{ $mahasiswa->angkatan }}</td>
            </tr>
            <tr>
                <th>IPK</th>
                <td>{{ $mahasiswa->ipk }}</td>
            </tr>
        </table>
    </div>
</div>
<h2 class="h5 mb-3">Matakuliah yang Diambil</h2>
<table class="table table-bordered bg-white">
    <thead>
        <tr>
            <th>Kode</th>
            <th>Nama</th>
            <th>SKS</th>
            <th>Semester</th>
            <th>Nilai</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($mahasiswa->matakuliahs as $matakuliah)
        <tr>
            <td>{{ $matakuliah->kode }}</td>
            <td>{{ $matakuliah->nama }}</td>
            <td>{{ $matakuliah->sks }}</td>
            <td>{{ $matakuliah->semester }}</td>
            <td>{{ number_format($matakuliah->pivot->nilai, 2) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="5">Belum ada matakuliah yang diambil</td>
        </tr>
        @endforelse
    </tbody>
</table>
<a href="{{ route('mahasiswa.data') }}" class="btn btn-secondary mt-3">Kembali</a>
@endsection