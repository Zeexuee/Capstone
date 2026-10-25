<?php

namespace Database\Factories;

use App\Enums\TingkatHarga;
use App\Models\HargaNasional;
use App\Models\Komoditas;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HargaNasional>
 */
class HargaNasionalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tanggal' => fake()->dateTimeBetween('2024-01-01', '2026-09-30')->format('Y-m-d'),
            'komoditas_id' => Komoditas::factory(),
            'tingkat_harga' => TingkatHarga::Konsumen,
            'harga' => fake()->randomFloat(2, 12000, 16000),
        ];
    }

    /**
     * Harga nasional di tingkat produsen.
     */
    public function produsen(): static
    {
        return $this->state(fn (): array => [
            'tingkat_harga' => TingkatHarga::Produsen,
        ]);
    }
}
