<?php

namespace Database\Seeders;

use App\Models\Mahasiswa;
use App\Models\Matakuliah;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(ProgramStudiSeeder::class);
        $this->call(MatakuliahSeeder::class);
        Mahasiswa::factory()->count(30)->create();

        $matakuliah = Matakuliah::all();

        Mahasiswa::all()->each(function (Mahasiswa $mahasiswa) use ($matakuliah): void {
            $mahasiswa->matakuliahs()->attach(
                $matakuliah->random((int) fake()->numberBetween(3, 5))->pluck('id'),
                ['nilai' => fake()->randomFloat(2, 50, 100)]
            );
        });
    }
}
