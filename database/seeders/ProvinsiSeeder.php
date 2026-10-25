<?php

namespace Database\Seeders;

use App\Enums\Pulau;
use App\Models\Provinsi;
use Illuminate\Database\Seeder;

/**
 * Dua puluh satu provinsi cakupan penelitian dengan kode provinsi BPS.
 * Seeder ini idempoten karena memakai upsert dengan kunci kode.
 */
class ProvinsiSeeder extends Seeder
{
    /**
     * @var array<string, array{0: string, 1: Pulau}>
     */
    private const array PROVINSI = [
        '11' => ['Aceh', Pulau::Sumatra],
        '12' => ['Sumatera Utara', Pulau::Sumatra],
        '13' => ['Sumatera Barat', Pulau::Sumatra],
        '14' => ['Riau', Pulau::Sumatra],
        '15' => ['Jambi', Pulau::Sumatra],
        '16' => ['Sumatera Selatan', Pulau::Sumatra],
        '17' => ['Bengkulu', Pulau::Sumatra],
        '18' => ['Lampung', Pulau::Sumatra],
        '19' => ['Kepulauan Bangka Belitung', Pulau::Sumatra],
        '21' => ['Kepulauan Riau', Pulau::Sumatra],
        '31' => ['DKI Jakarta', Pulau::Jawa],
        '32' => ['Jawa Barat', Pulau::Jawa],
        '33' => ['Jawa Tengah', Pulau::Jawa],
        '34' => ['DI Yogyakarta', Pulau::Jawa],
        '35' => ['Jawa Timur', Pulau::Jawa],
        '36' => ['Banten', Pulau::Jawa],
        '61' => ['Kalimantan Barat', Pulau::Kalimantan],
        '62' => ['Kalimantan Tengah', Pulau::Kalimantan],
        '63' => ['Kalimantan Selatan', Pulau::Kalimantan],
        '64' => ['Kalimantan Timur', Pulau::Kalimantan],
        '65' => ['Kalimantan Utara', Pulau::Kalimantan],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [];

        foreach (self::PROVINSI as $kode => [$nama, $pulau]) {
            $rows[] = ['kode' => (string) $kode, 'nama' => $nama, 'pulau' => $pulau->value];
        }

        Provinsi::query()->upsert($rows, ['kode'], ['nama', 'pulau']);
    }
}
