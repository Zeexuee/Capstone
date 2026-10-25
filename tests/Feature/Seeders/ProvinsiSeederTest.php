<?php

namespace Tests\Feature\Seeders;

use App\Enums\Pulau;
use App\Models\Provinsi;
use Database\Seeders\ProvinsiSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProvinsiSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_seeds_six_java_five_kalimantan_and_ten_sumatra_provinces(): void
    {
        $this->seed(ProvinsiSeeder::class);

        $this->assertSame(21, Provinsi::count());
        $this->assertSame(6, Provinsi::where('pulau', Pulau::Jawa)->count());
        $this->assertSame(5, Provinsi::where('pulau', Pulau::Kalimantan)->count());
        $this->assertSame(10, Provinsi::where('pulau', Pulau::Sumatra)->count());
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: Pulau}>
     */
    public static function provinsiDenganKodeBps(): array
    {
        return [
            'dki jakarta' => ['31', 'DKI Jakarta', Pulau::Jawa],
            'kalimantan utara' => ['65', 'Kalimantan Utara', Pulau::Kalimantan],
            'kepulauan riau dihitung sebagai sumatra' => ['21', 'Kepulauan Riau', Pulau::Sumatra],
            'bangka belitung dihitung sebagai sumatra' => ['19', 'Kepulauan Bangka Belitung', Pulau::Sumatra],
        ];
    }

    #[DataProvider('provinsiDenganKodeBps')]
    public function test_stores_each_province_under_its_bps_code_and_island(string $kode, string $nama, Pulau $pulau): void
    {
        $this->seed(ProvinsiSeeder::class);

        $provinsi = Provinsi::where('kode', $kode)->sole();

        $this->assertSame($nama, $provinsi->nama);
        $this->assertSame($pulau, $provinsi->pulau);
    }

    public function test_running_the_seeder_twice_does_not_duplicate_provinces(): void
    {
        $this->seed(ProvinsiSeeder::class);

        $this->seed(ProvinsiSeeder::class);

        $this->assertSame(21, Provinsi::count());
    }
}
