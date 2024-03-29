<?php


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


Route::get('trips', \App\Http\Controllers\TripController::class . '@index')
    ->name('trips.index');
Route::get('trips/search', \App\Http\Controllers\TripController::class . '@search')
    ->name('trips.search');

Route::post('trips/import', \App\Http\Controllers\TripController::class . '@import')
    ->name('trips.import');

Route::get('trips/calculate', \App\Http\Controllers\TripController::class . '@calculate')
    ->name('trips.calculate');

Route::get('trips/drivers-report', \App\Http\Controllers\DriverReportsController::class . '@index')
    ->name('drivers-report.index');
Route::get('trips/drivers-report/search', \App\Http\Controllers\DriverReportsController::class . '@search')
    ->name('drivers-report.search');

Route::get('trips/drivers-report/export', \App\Http\Controllers\DriverReportsController::class . '@export')
    ->name('drivers-report.export');
