<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel fakta harga harian.
     *
     * Tanggal tanpa data sengaja tidak memiliki baris, bukan diisi nol, agar celah
     * data terlihat apa adanya di grafik. Unique key mencakup seluruh dimensi
     * sehingga ETL dapat dijalankan ulang dengan upsert tanpa membuat baris ganda.
     * Foreign key ditambahkan setelah index komposit agar MySQL memakai index
     * tersebut dan tidak membuat index tambahan untuk kolom foreign key.
     */
    public function up(): void
    {
        Schema::create('harga_harian', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->foreignId('provinsi_id');
            $table->foreignId('komoditas_id');
            $table->string('tingkat_harga', 20);
            $table->string('jenis_pasar', 20)->default('umum');
            $table->string('sumber', 20);
            $table->decimal('harga', 12, 2);
            $table->timestamps();

            $table->unique(
                ['tanggal', 'provinsi_id', 'komoditas_id', 'tingkat_harga', 'jenis_pasar', 'sumber'],
                'harga_harian_unik',
            );
            $table->index(['provinsi_id', 'komoditas_id', 'tanggal'], 'harga_harian_wilayah_tanggal');
            $table->index(['komoditas_id', 'sumber', 'tingkat_harga', 'tanggal'], 'harga_harian_komoditas_tanggal');

            $table->foreign('provinsi_id')->references('id')->on('provinsi')->restrictOnDelete();
            $table->foreign('komoditas_id')->references('id')->on('komoditas')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harga_harian');
    }
};
