<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Enums\ModelPrediksi;
use App\Models\HargaHarian;
use App\Models\Komoditas;
use App\Models\MetrikModel;
use App\Models\Prediksi;
use App\Models\Provinsi;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PrediksiControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_shows_the_selected_model_when_it_meets_every_acceptance_criterion(): void
    {
        $this->siapkanPrediksi(mapeTerpilih: 0.64, rmseTerpilih: 110, maeTerpilih: 90);

        $response = $this->getJson(route('api.v1.prediksi', ['komoditas' => 'beras-medium', 'provinsi' => '31']));

        $response->assertOk()
            ->assertJsonPath('data.model.kode', 'xgboost')
            ->assertJsonPath('data.model.label', 'XGBoost')
            ->assertJsonPath('data.memakai_garis_dasar', false)
            ->assertJsonPath('data.prediksi', [['tanggal' => '2026-10-03', 'harga' => 15050], ['tanggal' => '2026-10-04', 'harga' => 15080]])
            ->assertJsonPath('data.garis_dasar', [['tanggal' => '2026-10-03', 'harga' => 15000], ['tanggal' => '2026-10-04', 'harga' => 15000]]);
    }

    public function test_falls_back_to_the_naive_baseline_when_the_selected_model_does_not_beat_it(): void
    {
        $this->siapkanPrediksi(mapeTerpilih: 0.64, rmseTerpilih: 130, maeTerpilih: 90);

        $response = $this->getJson(route('api.v1.prediksi', ['komoditas' => 'beras-medium', 'provinsi' => '31']));

        $response->assertOk()
            ->assertJsonPath('data.model.kode', 'naive')
            ->assertJsonPath('data.memakai_garis_dasar', true)
            ->assertJsonPath('data.prediksi.0.harga', 15000)
            ->assertJsonPath('data.garis_dasar', []);
    }

    public function test_falls_back_to_the_naive_baseline_when_mape_exceeds_the_threshold(): void
    {
        $this->siapkanPrediksi(mapeTerpilih: 3.2, rmseTerpilih: 110, maeTerpilih: 90, mapeNaive: 3.5);

        $response = $this->getJson(route('api.v1.prediksi', ['komoditas' => 'beras-medium', 'provinsi' => '31']));

        $response->assertOk()->assertJsonPath('data.memakai_garis_dasar', true);
    }

    public function test_reports_each_criterion_for_every_model(): void
    {
        $this->siapkanPrediksi(mapeTerpilih: 0.64, rmseTerpilih: 130, maeTerpilih: 90);

        $response = $this->getJson(route('api.v1.prediksi', ['komoditas' => 'beras-medium', 'provinsi' => '31']));

        $metrik = collect($response->json('data.metrik'))->keyBy('model');

        $this->assertSame(['lolos_mape' => true, 'lolos_r2' => true, 'mengalahkan_naive' => false, 'lolos' => false], collect($metrik['xgboost'])->only(['lolos_mape', 'lolos_r2', 'mengalahkan_naive', 'lolos'])->all());
        $this->assertNull($metrik['naive']['mengalahkan_naive']);
        $this->assertSame(['mape_maks' => 3, 'r2_min' => 0.7], $response->json('data.kriteria'));
    }

    public function test_returns_the_last_sixty_days_of_history_with_null_for_missing_days(): void
    {
        $this->siapkanPrediksi(mapeTerpilih: 0.64, rmseTerpilih: 110, maeTerpilih: 90);

        $response = $this->getJson(route('api.v1.prediksi', ['komoditas' => 'beras-medium', 'provinsi' => '31']));

        $response->assertOk()
            ->assertJsonCount(60, 'data.historis.tanggal')
            ->assertJsonPath('data.historis.tanggal.0', '2026-08-04')
            ->assertJsonPath('data.historis.tanggal.59', '2026-10-02')
            ->assertJsonPath('data.historis.harga.59', 15000)
            ->assertJsonPath('data.historis.harga.58', null);
    }

    public function test_returns_422_when_the_province_is_missing(): void
    {
        Komoditas::factory()->create(['kode' => 'beras-medium']);

        $response = $this->getJson(route('api.v1.prediksi', ['komoditas' => 'beras-medium']));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['provinsi' => 'Pilih satu provinsi untuk melihat prediksi.']);
    }

    private function siapkanPrediksi(float $mapeTerpilih, float $rmseTerpilih, float $maeTerpilih, float $mapeNaive = 0.82): void
    {
        $komoditas = Komoditas::factory()->create(['kode' => 'beras-medium']);
        $provinsi = Provinsi::factory()->create(['kode' => '31', 'nama' => 'DKI Jakarta']);

        MetrikModel::factory()->naive()->for($komoditas)->create(['mape' => $mapeNaive, 'rmse' => 120, 'mae' => 100, 'r2' => 0.84]);
        MetrikModel::factory()->terpilih()->for($komoditas)->create([
            'model' => ModelPrediksi::Xgboost,
            'mape' => $mapeTerpilih,
            'rmse' => $rmseTerpilih,
            'mae' => $maeTerpilih,
            'r2' => 0.9,
        ]);

        HargaHarian::factory()->for($komoditas)->for($provinsi)->create(['tanggal' => '2026-10-02', 'harga' => 15000]);
        HargaHarian::factory()->for($komoditas)->for($provinsi)->create(['tanggal' => '2026-09-30', 'harga' => 14980]);

        foreach (['2026-10-03' => [15000, 15050], '2026-10-04' => [15000, 15080]] as $tanggal => [$naive, $xgboost]) {
            Prediksi::factory()->for($komoditas)->for($provinsi)->create(['model' => ModelPrediksi::Naive, 'tanggal_sasaran' => $tanggal, 'harga_prediksi' => $naive]);
            Prediksi::factory()->for($komoditas)->for($provinsi)->create(['model' => ModelPrediksi::Xgboost, 'tanggal_sasaran' => $tanggal, 'harga_prediksi' => $xgboost]);
        }
    }
}
