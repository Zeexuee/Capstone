<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Metrik evaluasi per komoditas dan model, termasuk garis dasar Naive,
     * sehingga dasbor dapat menampilkan apakah model terpilih benar-benar
     * mengalahkan garis dasar sesuai kriteria penerimaan pada Tabel 3.6.
     */
    public function up(): void
    {
        Schema::create('metrik_model', function (Blueprint $table) {
            $table->id();
            $table->foreignId('komoditas_id');
            $table->string('model', 20);
            $table->decimal('mape', 8, 4);
            $table->decimal('rmse', 14, 4);
            $table->decimal('mae', 14, 4);
            $table->decimal('r2', 8, 4)->nullable();
            $table->date('periode_uji_mulai');
            $table->date('periode_uji_selesai');
            $table->boolean('terpilih')->default(false);
            $table->timestamps();

            $table->unique(['komoditas_id', 'model'], 'metrik_model_unik');

            $table->foreign('komoditas_id')->references('id')->on('komoditas')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metrik_model');
    }
};
