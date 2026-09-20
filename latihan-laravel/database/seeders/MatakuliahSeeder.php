<?php

namespace Database\Seeders;

use App\Models\Matakuliah;
use Illuminate\Database\Seeder;

class MatakuliahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $daftar = [
            ['kode' => 'TK2401', 'nama' => 'Pemrograman Web 2', 'sks' => 2, 'semester' => 4],
            ['kode' => 'TK2402', 'nama' => 'Sistem Kendali', 'sks' => 3, 'semester' => 4],
            ['kode' => 'TK2403', 'nama' => 'Internet of Things', 'sks' => 3, 'semester' => 5],
            ['kode' => 'TK2404', 'nama' => 'Keamanan Jaringan', 'sks' => 2, 'semester' => 5],
            ['kode' => 'TK2405', 'nama' => 'Metode Numerik', 'sks' => 2, 'semester' => 6],
        ];

        foreach ($daftar as $item) {
            Matakuliah::create($item);
        }
    }
}
