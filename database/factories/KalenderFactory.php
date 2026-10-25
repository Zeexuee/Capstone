<?php

namespace Database\Factories;

use App\Models\Kalender;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Kalender>
 */
class KalenderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tanggal = Carbon::instance(fake()->unique()->dateTimeBetween('2024-01-01', '2026-12-31'));

        return [
            'tanggal' => $tanggal->toDateString(),
            'tahun' => $tanggal->year,
            'bulan' => $tanggal->month,
            'minggu_iso' => $tanggal->isoWeek,
            'hari_iso' => $tanggal->dayOfWeekIso,
            'akhir_pekan' => $tanggal->isWeekend(),
            'hari_besar' => null,
            'musim_panen' => false,
        ];
    }
}
