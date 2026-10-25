<?php

namespace Database\Factories;

use App\Enums\ModelPrediksi;
use App\Models\Komoditas;
use App\Models\Prediksi;
use App\Models\Provinsi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Prediksi>
 */
class PrediksiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'provinsi_id' => Provinsi::factory(),
            'komoditas_id' => Komoditas::factory(),
            'model' => ModelPrediksi::RandomForest,
            'tanggal_sasaran' => fake()->dateTimeBetween('2026-10-01', '2026-12-31')->format('Y-m-d'),
            'harga_prediksi' => fake()->randomFloat(2, 10000, 20000),
            'dihasilkan_pada' => now(),
        ];
    }
}
