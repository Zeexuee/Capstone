<?php

namespace Tests\Feature\Seeders;

use App\Enums\KelompokKomoditas;
use App\Enums\ModelPrediksi;
use App\Enums\TingkatHarga;
use App\Models\HargaHarian;
use App\Models\HargaNasional;
use App\Models\IndikatorHarga;
use App\Models\Komoditas;
use App\Models\MetrikModel;
use App\Models\Prediksi;
use App\Models\Provinsi;
use Database\Seeders\DataTiruanSeeder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DataTiruanSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_fills_every_fact_table_for_all_four_commodities(): void
    {
        $this->jalankanSeederTiruan();

        $this->assertSame(4, HargaHarian::distinct()->count('komoditas_id'));
        $this->assertSame(4, HargaNasional::distinct()->count('komoditas_id'));
        $this->assertSame(4, IndikatorHarga::distinct()->count('komoditas_id'));
        $this->assertSame(21 * 4 * 3 * 7, Prediksi::count());
        $this->assertSame(4 * 3, MetrikModel::count());
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public static function celahDisengaja(): array
    {
        return [
            'kalimantan utara agustus' => ['65', '2026-08-10', '2026-08-21'],
            'jawa timur september' => ['35', '2026-09-07', '2026-09-18'],
        ];
    }

    #[DataProvider('celahDisengaja')]
    public function test_leaves_no_province_rows_inside_a_deliberate_gap(string $kodeProvinsi, string $mulai, string $selesai): void
    {
        $this->jalankanSeederTiruan();
        $provinsi = Provinsi::where('kode', $kodeProvinsi)->sole();

        $dalamCelah = HargaHarian::whereBelongsTo($provinsi)->whereBetween('tanggal', [$mulai, $selesai])->count();
        $indikatorDalamCelah = IndikatorHarga::whereBelongsTo($provinsi)->whereBetween('tanggal', [$mulai, $selesai])->count();
        $luarCelah = HargaHarian::whereBelongsTo($provinsi)->whereNotBetween('tanggal', [$mulai, $selesai])->count();

        $this->assertSame(0, $dalamCelah);
        $this->assertSame(0, $indikatorDalamCelah);
        $this->assertGreaterThan(0, $luarCelah);
    }

    public function test_keeps_the_national_price_on_a_day_when_a_province_has_a_gap(): void
    {
        $this->jalankanSeederTiruan();

        $this->assertSame(6, HargaNasional::where('tanggal', '2026-08-12')->count());
    }

    public function test_records_producer_prices_for_rice_only(): void
    {
        $this->jalankanSeederTiruan();

        $kelompokDenganHargaProdusen = Komoditas::whereHas(
            'hargaHarian',
            fn (Builder $kueri): Builder => $kueri->where('tingkat_harga', TingkatHarga::Produsen),
        )->get()->pluck('kelompok')->unique()->values()->all();

        $this->assertSame([KelompokKomoditas::Beras], $kelompokDenganHargaProdusen);
    }

    public function test_indicator_compares_the_province_price_with_the_bapanas_national_price(): void
    {
        $this->jalankanSeederTiruan();
        $indikator = IndikatorHarga::orderBy('id')->firstOrFail();

        $hargaNasional = HargaNasional::whereBelongsTo($indikator->komoditas)
            ->where('tingkat_harga', $indikator->tingkat_harga)
            ->where('tanggal', $indikator->tanggal->toDateString())
            ->sole();
        $hargaProvinsi = HargaHarian::whereBelongsTo($indikator->provinsi)
            ->whereBelongsTo($indikator->komoditas)
            ->where('tingkat_harga', $indikator->tingkat_harga)
            ->where('tanggal', $indikator->tanggal->toDateString())
            ->sole();
        $selisihTidakCocok = IndikatorHarga::whereRaw('abs(harga - rata_rata_nasional - selisih) > 0.005')->count();

        $this->assertSame($hargaNasional->harga, $indikator->rata_rata_nasional);
        $this->assertSame($hargaProvinsi->harga, $indikator->harga);
        $this->assertSame(0, $selisihTidakCocok);
    }

    public function test_naive_prediction_repeats_the_last_observed_consumer_price(): void
    {
        $this->jalankanSeederTiruan();
        $provinsi = Provinsi::where('kode', '31')->sole();
        $komoditas = Komoditas::where('kode', 'beras-medium')->sole();

        $hargaTerakhir = HargaHarian::whereBelongsTo($provinsi)
            ->whereBelongsTo($komoditas)
            ->where('tingkat_harga', TingkatHarga::Konsumen)
            ->orderByDesc('tanggal')
            ->value('harga');
        $prediksiNaive = Prediksi::whereBelongsTo($provinsi)
            ->whereBelongsTo($komoditas)
            ->where('model', ModelPrediksi::Naive)
            ->orderBy('tanggal_sasaran')
            ->get();

        $this->assertCount(7, $prediksiNaive);
        $this->assertSame('2026-10-01', $prediksiNaive->first()->tanggal_sasaran->toDateString());
        $this->assertSame([$hargaTerakhir], $prediksiNaive->pluck('harga_prediksi')->unique()->values()->all());
    }

    public function test_selected_model_beats_the_naive_baseline_and_meets_the_acceptance_criteria(): void
    {
        $this->jalankanSeederTiruan();

        foreach (Komoditas::all() as $komoditas) {
            $terpilih = MetrikModel::whereBelongsTo($komoditas)->where('terpilih', true)->sole();
            $naive = MetrikModel::whereBelongsTo($komoditas)->where('model', ModelPrediksi::Naive)->sole();

            $this->assertNotSame(ModelPrediksi::Naive, $terpilih->model, $komoditas->kode);
            $this->assertLessThan((float) $naive->mape, (float) $terpilih->mape, $komoditas->kode);
            $this->assertLessThan(3.0, (float) $terpilih->mape, $komoditas->kode);
            $this->assertGreaterThanOrEqual(0.70, (float) $terpilih->r2, $komoditas->kode);
        }
    }

    public function test_does_not_add_rows_when_a_fact_table_already_holds_data(): void
    {
        HargaHarian::factory()->create();

        $this->jalankanSeederTiruan();

        $this->assertSame(1, HargaHarian::count());
        $this->assertSame(0, Prediksi::count());
    }

    public function test_does_not_run_in_production(): void
    {
        $this->app['env'] = 'production';

        (new DataTiruanSeeder('2026-08-01', '2026-09-30', 7))->setContainer($this->app)->__invoke();

        $this->assertSame(0, Provinsi::count());
        $this->assertSame(0, HargaHarian::count());
    }

    private function jalankanSeederTiruan(): void
    {
        $this->app->instance(DataTiruanSeeder::class, new DataTiruanSeeder(
            tanggalAwal: '2026-08-01',
            tanggalAkhir: '2026-09-30',
            hariPrediksi: 7,
        ));

        $this->seed(DataTiruanSeeder::class);
    }
}
