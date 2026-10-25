<?php

use App\Http\Controllers\DasborController;
use Illuminate\Support\Facades\Route;

Route::get('/', DasborController::class)->name('dasbor');
