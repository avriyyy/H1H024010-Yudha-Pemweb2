<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MatakuliahController extends Controller
{
    public function index(Request $request)
    {
        $daftarMatakuliah = [
            ['kode' => 'TK2401', 'nama' => 'Pemrograman Web 2', 'sks' => 2],
            ['kode' => 'TK2402', 'nama' => 'Sistem Kendali', 'sks' => 3],
            ['kode' => 'TK2403', 'nama' => 'Internet of Things', 'sks' => 3],
            ['kode' => 'TK2404', 'nama' => 'Keamanan Jaringan', 'sks' => 2],
            ['kode' => 'TK2405', 'nama' => 'Metode Numerik', 'sks' => 2],
        ];

        $kataKunci = $request->query('q', '');

        if ($kataKunci !== '') {
            $daftarMatakuliah = array_values(array_filter(
                $daftarMatakuliah,
                fn ($matakuliah) => str_contains(
                    strtolower($matakuliah['kode'].' '.$matakuliah['nama']),
                    strtolower($kataKunci)
                )
            ));
        }

        return view('matakuliah.index', [
            'daftarMatakuliah' => $daftarMatakuliah,
            'kataKunci' => $kataKunci,
        ]);
    }

    public function show(string $kode)
    {
        return view('matakuliah.show', ['kode' => $kode]);
    }
}
