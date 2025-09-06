<?php

use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;

Route::post('/upload', [FileController::class, 'upload'])->name('file.upload');
Route::delete('/file', [FileController::class, 'delete'])->name('file.delete');
