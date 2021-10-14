<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('list', [App\Http\Controllers\ApiController::class, 'listAllOrders']);
Route::get('delete/{id}', [App\Http\Controllers\ApiController::class, 'delete']);
Route::post('save', [App\Http\Controllers\ApiController::class, 'store']);
Route::get('discount/{id}', [App\Http\Controllers\ApiController::class, 'calculateDiscount']);

