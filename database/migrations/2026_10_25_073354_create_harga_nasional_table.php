<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Harga rata-rata nasional harian dari Panel Harga Pangan Bapanas.
     *
     * Sesuai keputusan tim, acuan rata-rata nasional adalah angka nasional yang
     * diterbitkan Bapanas, bukan rata-rata dari 21 provinsi cakupan penelitian.
     * Karena sumbernya selalu Bapanas, tabel ini tidak memiliki kolom sumber.
     * Unique key diurutkan komoditas, tingkat harga, lalu tanggal agar sekaligus
     * melayani kueri deret waktu dan foreign key komoditas tanpa index tambahan.
     */
    public function up(): void
    {
        Schema::create('harga_nasional', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->foreignId('komoditas_id');
            $table->string('tingkat_harga', 20);
            $table->decimal('harga', 12, 2);
            $table->timestamps();

            $table->unique(['komoditas_id', 'tingkat_harga', 'tanggal'], 'harga_nasional_unik');

            $table->foreign('komoditas_id')->references('id')->on('komoditas')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harga_nasional');
    }
};
