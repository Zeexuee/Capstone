<?php

namespace Database\Seeders;

use App\Models\Kalender;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Dimensi waktu 1 Januari 2024 sampai 31 Desember 2026.
 *
 * Tanggal hari besar mengikuti hari libur nasional yang ditetapkan SKB 3 Menteri,
 * diperiksa silang dengan daftar OfficeHolidays 2024 dan 2025 serta daftar
 * hari libur Indonesia 2026. Untuk Idul Fitri hanya 1 Syawal yang dicatat.
 * Awal Ramadan memakai penetapan pemerintah hasil sidang isbat Kementerian Agama.
 * Tahun 2027 belum dicatat karena sumber yang ditemukan saling bertentangan.
 * Sumber dan alasan setiap tanggal ada di CATATAN_DATA.md.
 */
class KalenderSeeder extends Seeder
{
    public const string TANGGAL_AWAL = '2024-01-01';

    public const string TANGGAL_AKHIR = '2026-12-31';

    /**
     * @var array<string, string>
     */
    public const array HARI_BESAR = [
        '2024-02-10' => 'Tahun Baru Imlek',
        '2024-03-12' => 'Awal Ramadan',
        '2024-04-10' => 'Idul Fitri',
        '2024-06-17' => 'Idul Adha',
        '2024-12-25' => 'Natal',
        '2025-01-29' => 'Tahun Baru Imlek',
        '2025-03-01' => 'Awal Ramadan',
        '2025-03-31' => 'Idul Fitri',
        '2025-06-06' => 'Idul Adha',
        '2025-12-25' => 'Natal',
        '2026-02-17' => 'Tahun Baru Imlek',
        '2026-02-19' => 'Awal Ramadan',
        '2026-03-21' => 'Idul Fitri',
        '2026-05-27' => 'Idul Adha',
        '2026-12-25' => 'Natal',
    ];

    /**
     * Bulan panen raya padi. Ini asumsi kerja yang perlu dikonfirmasi dengan data
     * luas panen bulanan BPS sebelum dipakai sebagai fitur model.
     *
     * @var list<int>
     */
    public const array BULAN_MUSIM_PANEN = [3, 4];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [];

        foreach (CarbonPeriod::create(self::TANGGAL_AWAL, self::TANGGAL_AKHIR) as $hari) {
            $rows[] = $this->baris(Carbon::instance($hari));
        }

        foreach (array_chunk($rows, 100) as $potongan) {
            Kalender::query()->upsert(
                $potongan,
                ['tanggal'],
                ['tahun', 'bulan', 'minggu_iso', 'hari_iso', 'akhir_pekan', 'hari_besar', 'musim_panen'],
            );
        }
    }

    /**
     * @return array{tanggal: string, tahun: int, bulan: int, minggu_iso: int, hari_iso: int, akhir_pekan: bool, hari_besar: ?string, musim_panen: bool}
     */
    private function baris(Carbon $hari): array
    {
        $tanggal = $hari->toDateString();

        return [
            'tanggal' => $tanggal,
            'tahun' => $hari->year,
            'bulan' => $hari->month,
            'minggu_iso' => $hari->isoWeek,
            'hari_iso' => $hari->dayOfWeekIso,
            'akhir_pekan' => $hari->isWeekend(),
            'hari_besar' => self::HARI_BESAR[$tanggal] ?? null,
            'musim_panen' => in_array($hari->month, self::BULAN_MUSIM_PANEN, true),
        ];
    }
}
