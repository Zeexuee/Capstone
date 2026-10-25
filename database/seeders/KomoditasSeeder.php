<?php

namespace Database\Seeders;

use App\Enums\KelompokKomoditas;
use App\Enums\SatuanHarga;
use App\Models\Komoditas;
use Illuminate\Database\Seeder;

/**
 * Varian komoditas yang diteliti beserta satuan bakunya.
 *
 * Seluruh varian minyak goreng memakai satuan liter. Sebagian sumber mencatat
 * minyak goreng curah per kilogram; baris seperti itu tidak dikonversi dan tidak
 * dimuat, karena belum ada faktor konversi resmi yang terverifikasi.
 * Rincian keputusannya ada di CATATAN_DATA.md.
 */
class KomoditasSeeder extends Seeder
{
    /**
     * @var list<array{kode: string, nama: string, kelompok: KelompokKomoditas, satuan: SatuanHarga}>
     */
    private const array KOMODITAS = [
        ['kode' => 'beras-medium', 'nama' => 'Beras Medium', 'kelompok' => KelompokKomoditas::Beras, 'satuan' => SatuanHarga::Kilogram],
        ['kode' => 'beras-premium', 'nama' => 'Beras Premium', 'kelompok' => KelompokKomoditas::Beras, 'satuan' => SatuanHarga::Kilogram],
        ['kode' => 'minyak-goreng-kemasan', 'nama' => 'Minyak Goreng Kemasan', 'kelompok' => KelompokKomoditas::MinyakGoreng, 'satuan' => SatuanHarga::Liter],
        ['kode' => 'minyak-goreng-curah', 'nama' => 'Minyak Goreng Curah', 'kelompok' => KelompokKomoditas::MinyakGoreng, 'satuan' => SatuanHarga::Liter],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = array_map(fn (array $komoditas): array => [
            'kode' => $komoditas['kode'],
            'nama' => $komoditas['nama'],
            'kelompok' => $komoditas['kelompok']->value,
            'satuan' => $komoditas['satuan']->value,
        ], self::KOMODITAS);

        Komoditas::query()->upsert($rows, ['kode'], ['nama', 'kelompok', 'satuan']);
    }
}
