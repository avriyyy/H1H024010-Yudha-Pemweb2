<?php

use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\MatakuliahController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('mahasiswa');
});

Route::get('/salam', function () {
    return 'Selamat datang di Pemrograman Web II';
});

Route::get('/mahasiswa/{nim}', function (string $nim) {
    return 'Data mahasiswa dengan NIM '.$nim;
});

Route::get('/matakuliah/{kode?}', function (?string $kode = null) {
    if ($kode === null) {
        return 'Menampilkan seluruh matakuliah';
    }

    return 'Menampilkan matakuliah kode '.$kode;
});

Route::get('/semester/{angka}', function (int $angka) {
    return 'Semester ke '.$angka;
})->whereNumber('angka');

Route::get('/data-mahasiswa', [MahasiswaController::class,
    'index'])->name('mahasiswa.index');

Route::get('/data-mahasiswa/{nim}', [MahasiswaController::class,
    'show'])->name('mahasiswa.show');

Route::get('/cari-mahasiswa', [MahasiswaController::class, 'cari']);

Route::get('/data-matakuliah', [MatakuliahController::class, 'index'])->name('matakuliah.index');

Route::get('/data-matakuliah/{kode}', [MatakuliahController::class, 'show'])->name('matakuliah.show');
