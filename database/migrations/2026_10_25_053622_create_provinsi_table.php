<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dimensi wilayah. Kode memakai kode provinsi BPS agar ETL dapat mencocokkan
     * wilayah tanpa bergantung pada ejaan nama yang berbeda antarsumber.
     */
    public function up(): void
    {
        Schema::create('provinsi', function (Blueprint $table) {
            $table->id();
            $table->char('kode', 2)->unique();
            $table->string('nama', 60)->unique();
            $table->string('pulau', 20)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provinsi');
    }
};
