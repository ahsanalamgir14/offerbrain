<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();
Route::any('/register', function() {
    return view('auth.login');
});

Route::any('/get_records', [OrdersController::class, 'pull_orders_jan']);
Route::any('/update_profiles', [ProfileController::class, 'update_profiles']);

Route::group(['middleware' => 'auth'], function () {
Route::any('/{any}', [HomeController::class, 'index'])->where('any', '^(?!api).*$');
});
