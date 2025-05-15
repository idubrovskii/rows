<?php

use App\Http\Controllers\RowsController;
use Illuminate\Support\Facades\Route;


Route::middleware('auth.basic')->group(function () {

    Route::get('/', function () {
        return view('upload');
    });

    Route::post('/upload', [RowsController::class, 'upload'])->name('file.upload');

});

Route::get('list', [RowsController::class, 'index']);
