<?php

namespace Database\Factories;

use App\Enums\TingkatHarga;
use App\Models\IndikatorHarga;
use App\Models\Komoditas;
use App\Models\Provinsi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IndikatorHarga>
 */
class IndikatorHargaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $rataRataNasional = fake()->randomFloat(2, 12000, 16000);
        $harga = fake()->randomFloat(2, 10000, 20000);
        $selisih = round($harga - $rataRataNasional, 2);

        return [
            'tanggal' => fake()->dateTimeBetween('2024-01-01', '2026-09-30')->format('Y-m-d'),
            'provinsi_id' => Provinsi::factory(),
            'komoditas_id' => Komoditas::factory(),
            'tingkat_harga' => TingkatHarga::Konsumen,
            'harga' => $harga,
            'rata_rata_nasional' => $rataRataNasional,
            'selisih' => $selisih,
            'selisih_persen' => round($selisih / $rataRataNasional * 100, 2),
        ];
    }
}
