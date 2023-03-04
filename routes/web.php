<?php

use App\Http\Controllers\DropZoneController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::controller(DropZoneController::class)->group(function () {
    // Route::get('file-upload', 'index');
    Route::post('file-upload', 'store')->name('file.store');
    Route::delete('file-upload/{filename}', 'delete')->name('file.delete');
});
