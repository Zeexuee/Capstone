<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed data acuan. Setiap seeder idempoten, sehingga db:seed aman dijalankan ulang.
     */
    public function run(): void
    {
        $this->call([
            ProvinsiSeeder::class,
            KomoditasSeeder::class,
            KalenderSeeder::class,
        ]);
    }
}
