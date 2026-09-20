<?php

namespace Database\Factories;

use App\Models\Matakuliah;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Matakuliah>
 */
class MatakuliahFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode' => strtoupper(fake()->unique()->lexify('MK####')),
            'nama' => fake()->unique()->words(3, true),
            'sks' => fake()->randomElement([2, 3]),
            'semester' => fake()->numberBetween(1, 8),
        ];
    }
}
