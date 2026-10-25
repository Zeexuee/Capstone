<?php

namespace Tests\Feature\Models;

use App\Enums\TingkatHarga;
use App\Models\HargaNasional;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class HargaNasionalTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_rejects_a_second_national_price_for_the_same_day_commodity_and_level(): void
    {
        $harga = HargaNasional::factory()->create(['tanggal' => '2026-03-02']);

        $this->expectException(UniqueConstraintViolationException::class);

        HargaNasional::factory()->for($harga->komoditas)->create(['tanggal' => '2026-03-02']);
    }

    public function test_accepts_a_producer_price_on_the_same_day_as_the_consumer_price(): void
    {
        $konsumen = HargaNasional::factory()->create(['tanggal' => '2026-03-02']);

        HargaNasional::factory()->produsen()->for($konsumen->komoditas)->create(['tanggal' => '2026-03-02']);

        $this->assertSame(2, HargaNasional::count());
    }

    public function test_upsert_on_the_unique_key_updates_the_price_instead_of_adding_a_row(): void
    {
        $harga = HargaNasional::factory()->create(['tanggal' => '2026-03-02', 'harga' => 14000]);

        HargaNasional::query()->upsert([[
            'komoditas_id' => $harga->komoditas_id,
            'tingkat_harga' => TingkatHarga::Konsumen->value,
            'tanggal' => '2026-03-02',
            'harga' => 14250,
        ]], HargaNasional::KUNCI_UNIK, ['harga']);

        $this->assertSame(1, HargaNasional::count());
        $this->assertSame('14250.00', $harga->fresh()->harga);
    }
}
