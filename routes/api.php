<?php

use App\Http\Controllers\Api\V1\PerbandinganController;
use App\Http\Controllers\Api\V1\PrediksiController;
use App\Http\Controllers\Api\V1\RingkasanController;
use App\Http\Controllers\Api\V1\TrenController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
| Endpoint dasbor bersifat publik dan baca-saja, sehingga tidak memerlukan
| autentikasi. Pembatasan laju per IP mengurangi penyalahgunaan.
*/
Route::prefix('v1')->name('api.v1.')->middleware('throttle:dasbor')->group(function (): void {
    Route::get('ringkasan', RingkasanController::class)->name('ringkasan');
    Route::get('tren', TrenController::class)->name('tren');
    Route::get('perbandingan', PerbandinganController::class)->name('perbandingan');
    Route::get('prediksi', PrediksiController::class)->name('prediksi');
});
