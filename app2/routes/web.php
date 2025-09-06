<?php

use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/upload');
});

Route::get('/upload', [UploadController::class, 'index'])->name('upload.form');
Route::post('/upload', [UploadController::class, 'store'])->name('file.upload');
