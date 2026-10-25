<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Enums\Pulau;
use App\Models\HargaNasional;
use App\Models\IndikatorHarga;
use App\Models\Komoditas;
use App\Models\Provinsi;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PerbandinganControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_ranks_provinces_from_the_cheapest_with_their_gap_to_the_national_average(): void
    {
        $this->siapkanData();

        $response = $this->getJson(route('api.v1.perbandingan', ['komoditas' => 'beras-medium']));

        $response->assertOk()
            ->assertJsonPath('data.tanggal', '2026-10-01')
            ->assertJsonPath('data.rata_rata_nasional', 14700)
            ->assertJsonCount(2, 'data.provinsi')
            ->assertJsonPath('data.provinsi.0.peringkat', 1)
            ->assertJsonPath('data.provinsi.0.nama', 'Jawa Tengah')
            ->assertJsonPath('data.provinsi.0.selisih', -700)
            ->assertJsonPath('data.provinsi.0.selisih_persen', -4.76)
            ->assertJsonPath('data.provinsi.1.peringkat', 2)
            ->assertJsonPath('data.provinsi.1.nama', 'DKI Jakarta');
    }

    public function test_lists_provinces_without_data_on_the_date_instead_of_hiding_them(): void
    {
        $this->siapkanData();

        $response = $this->getJson(route('api.v1.perbandingan', ['komoditas' => 'beras-medium']));

        $response->assertOk()
            ->assertJsonCount(1, 'data.provinsi_tanpa_data')
            ->assertJsonPath('data.provinsi_tanpa_data.0.nama', 'Sumatera Barat');
    }

    public function test_compares_provinces_on_the_requested_date(): void
    {
        $data = $this->siapkanData();
        IndikatorHarga::factory()->for($data['komoditas'])->for($data['sumatera_barat'])->create([
            'tanggal' => '2026-09-01',
            'harga' => 15500,
            'rata_rata_nasional' => 14000,
            'selisih' => 1500,
            'selisih_persen' => 10.71,
        ]);

        $response = $this->getJson(route('api.v1.perbandingan', ['komoditas' => 'beras-medium', 'tanggal' => '2026-09-01']));

        $response->assertOk()
            ->assertJsonPath('data.tanggal', '2026-09-01')
            ->assertJsonPath('data.rata_rata_nasional', 14000)
            ->assertJsonCount(1, 'data.provinsi')
            ->assertJsonPath('data.provinsi.0.nama', 'Sumatera Barat');
    }

    public function test_returns_only_provinces_on_the_selected_island(): void
    {
        $this->siapkanData();

        $response = $this->getJson(route('api.v1.perbandingan', ['komoditas' => 'beras-medium', 'pulau' => 'sumatra']));

        $response->assertOk()
            ->assertJsonCount(0, 'data.provinsi')
            ->assertJsonPath('data.provinsi_tanpa_data.0.nama', 'Sumatera Barat');
    }

    public function test_returns_422_for_a_date_in_the_wrong_format(): void
    {
        $this->siapkanData();

        $response = $this->getJson(route('api.v1.perbandingan', ['komoditas' => 'beras-medium', 'tanggal' => '01-10-2026']));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['tanggal' => 'Tanggal harus berformat TTTT-BB-HH.']);
    }

    /**
     * @return array{komoditas: Komoditas, sumatera_barat: Provinsi}
     */
    private function siapkanData(): array
    {
        $komoditas = Komoditas::factory()->create(['kode' => 'beras-medium']);
        $jawaTengah = Provinsi::factory()->create(['kode' => '33', 'nama' => 'Jawa Tengah', 'pulau' => Pulau::Jawa]);
        $dkiJakarta = Provinsi::factory()->create(['kode' => '31', 'nama' => 'DKI Jakarta', 'pulau' => Pulau::Jawa]);
        $sumateraBarat = Provinsi::factory()->create(['kode' => '13', 'nama' => 'Sumatera Barat', 'pulau' => Pulau::Sumatra]);

        HargaNasional::factory()->for($komoditas)->create(['tanggal' => '2026-09-01', 'harga' => 14000]);
        HargaNasional::factory()->for($komoditas)->create(['tanggal' => '2026-10-01', 'harga' => 14700]);

        foreach ([[$dkiJakarta, 15000, 300, 2.04], [$jawaTengah, 14000, -700, -4.76]] as [$provinsi, $harga, $selisih, $persen]) {
            IndikatorHarga::factory()->for($komoditas)->for($provinsi)->create([
                'tanggal' => '2026-10-01',
                'harga' => $harga,
                'rata_rata_nasional' => 14700,
                'selisih' => $selisih,
                'selisih_persen' => $persen,
            ]);
        }

        return ['komoditas' => $komoditas, 'sumatera_barat' => $sumateraBarat];
    }
}
