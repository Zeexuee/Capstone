<?php

namespace Database\Factories;

use App\Enums\ModelPrediksi;
use App\Models\Komoditas;
use App\Models\MetrikModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MetrikModel>
 */
class MetrikModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'komoditas_id' => Komoditas::factory(),
            'model' => ModelPrediksi::RandomForest,
            'mape' => fake()->randomFloat(4, 0.5, 3),
            'rmse' => fake()->randomFloat(4, 50, 400),
            'mae' => fake()->randomFloat(4, 40, 300),
            'r2' => fake()->randomFloat(4, 0.7, 0.95),
            'periode_uji_mulai' => '2026-01-01',
            'periode_uji_selesai' => '2026-06-30',
            'terpilih' => false,
        ];
    }

    /**
     * Metrik garis dasar Naive yang wajib dikalahkan oleh model ensemble.
     */
    public function naive(): static
    {
        return $this->state(fn (): array => [
            'model' => ModelPrediksi::Naive,
        ]);
    }

    /**
     * Model yang terpilih untuk ditampilkan di dasbor.
     */
    public function terpilih(): static
    {
        return $this->state(fn (): array => [
            'terpilih' => true,
        ]);
    }
}
