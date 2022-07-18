<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\Quickbook;

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
Route::get('/quickbook',[Quickbook::class, 'index']);
Route::get('/callback.php',[Quickbook::class, 'processCode']);
Route::get('/apiCall',[Quickbook::class, 'apicall']);
Route::POST('/refreshToken',[Quickbook::class, 'refreshToken']);

Route::get('/accounts_all',[Quickbook::class, 'accounts_all'])->name('/accounts_all');


Auth::routes();
Route::any('/register', function() {
    return view('auth.login');
});

Route::any('/get_records', [OrdersController::class, 'pull_orders_jan']);
Route::any('/update_profiles', [ProfileController::class, 'update_profiles']);
Route::any('/insert-missing-history', [OrdersController::class, 'insert_missing_history']);
Route::post('/insert-missing-result', [OrdersController::class, 'insert_missing_result'])->name('missing-history.post');

Route::group(['middleware' => 'auth'], function () {
Route::any('/{any}', [HomeController::class, 'index'])->where('any', '^(?!api).*$');
});


