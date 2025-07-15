<?php

use App\Http\Controllers\EcpayController;
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

/**綠界金流送單*/
Route::get('ecpay', [EcpayController::class, "ecpay"]);

/**綠界金流Callback*/
Route::post('ecpay/callback', [EcpayController::class, "ecpayCallback"]);


/**test*/
Route::get('test/ecpay', [TestController::class, "ecpay"]);
Route::post('test/ecpay/callback', [TestController::class, "ecpayCallback"]);