<?php

use App\Http\Controllers\TestController;
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


Route::get('/react', function () {
    return view('react');
});




/**test*/
Route::get('test/ecpay', [TestController::class, "ecpay"]);
Route::get('test/ecpay/callback', [TestController::class, "ecpayCallback"]);