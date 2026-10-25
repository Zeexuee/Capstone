<?php

namespace Tests\Feature\Seeders;

use App\Models\Kalender;
use Database\Seeders\KalenderSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class KalenderSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_covers_every_day_from_2024_through_2026_without_gaps(): void
    {
        $this->seed(KalenderSeeder::class);

        $this->assertSame(1096, Kalender::count());
        $this->assertSame('2024-01-01', Kalender::query()->toBase()->min('tanggal'));
        $this->assertSame('2026-12-31', Kalender::query()->toBase()->max('tanggal'));
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function hariBesarTerverifikasi(): array
    {
        return [
            'imlek 2024' => ['2024-02-10', 'Tahun Baru Imlek'],
            'awal ramadan 2024' => ['2024-03-12', 'Awal Ramadan'],
            'awal ramadan 2025' => ['2025-03-01', 'Awal Ramadan'],
            'awal ramadan 2026' => ['2026-02-19', 'Awal Ramadan'],
            'idul fitri 2024' => ['2024-04-10', 'Idul Fitri'],
            'idul fitri 2025' => ['2025-03-31', 'Idul Fitri'],
            'idul adha 2025' => ['2025-06-06', 'Idul Adha'],
            'idul fitri 2026' => ['2026-03-21', 'Idul Fitri'],
            'idul adha 2026' => ['2026-05-27', 'Idul Adha'],
            'natal 2026' => ['2026-12-25', 'Natal'],
        ];
    }

    #[DataProvider('hariBesarTerverifikasi')]
    public function test_marks_national_religious_holidays_on_their_official_date(string $tanggal, string $hariBesar): void
    {
        $this->seed(KalenderSeeder::class);

        $this->assertSame($hariBesar, Kalender::findOrFail($tanggal)->hari_besar);
    }

    public function test_records_only_the_first_day_of_idul_fitri(): void
    {
        $this->seed(KalenderSeeder::class);

        $this->assertNull(Kalender::findOrFail('2025-04-01')->hari_besar);
        $this->assertSame(15, Kalender::whereNotNull('hari_besar')->count());
    }

    public function test_flags_the_harvest_season_only_in_march_and_april(): void
    {
        $this->seed(KalenderSeeder::class);

        $bulanPanen = Kalender::where('musim_panen', true)
            ->distinct()
            ->orderBy('bulan')
            ->pluck('bulan')
            ->all();

        $this->assertSame([3, 4], $bulanPanen);
    }

    public function test_marks_saturday_and_sunday_as_weekend_but_not_monday(): void
    {
        $this->seed(KalenderSeeder::class);

        $sabtu = Kalender::findOrFail('2026-10-03');
        $minggu = Kalender::findOrFail('2026-10-04');
        $senin = Kalender::findOrFail('2026-10-05');

        $this->assertTrue($sabtu->akhir_pekan);
        $this->assertTrue($minggu->akhir_pekan);
        $this->assertFalse($senin->akhir_pekan);
        $this->assertSame(1, $senin->hari_iso);
    }

    public function test_running_the_seeder_twice_does_not_duplicate_days(): void
    {
        $this->seed(KalenderSeeder::class);

        $this->seed(KalenderSeeder::class);

        $this->assertSame(1096, Kalender::count());
    }
}
