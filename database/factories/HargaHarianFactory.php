<?php

namespace Database\Factories;

use App\Enums\JenisPasar;
use App\Enums\SumberData;
use App\Enums\TingkatHarga;
use App\Models\HargaHarian;
use App\Models\Komoditas;
use App\Models\Provinsi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HargaHarian>
 */
class HargaHarianFactory extends Factory
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
            'provinsi_id' => Provinsi::factory(),
            'komoditas_id' => Komoditas::factory(),
            'tingkat_harga' => TingkatHarga::Konsumen,
            'jenis_pasar' => JenisPasar::Umum,
            'sumber' => SumberData::Bapanas,
            'harga' => fake()->randomFloat(2, 10000, 20000),
        ];
    }

    /**
     * Harga yang tercatat di tingkat produsen.
     */
    public function produsen(): static
    {
        return $this->state(fn (): array => [
            'tingkat_harga' => TingkatHarga::Produsen,
        ]);
    }

    /**
     * Harga dari PIHPS Bank Indonesia sebagai sumber pembanding.
     */
    public function dariPihps(): static
    {
        return $this->state(fn (): array => [
            'sumber' => SumberData::Pihps,
        ]);
    }
}
