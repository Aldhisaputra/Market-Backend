<?php

use App\Http\Controllers\API\FoodController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Sentry\Tracing\Transaction;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function(){
Route::get('user', [UserController::class, 'login']);
Route::post('login', [UserController::class, 'updateProfile']);
Route::post('user/photo', [UserController::class, 'UpdatePhoto']);
Route::post('logout', [UserController::class, 'logout']);

Route::get('transaction', [TransactionController::class, 'all']);

});

Route::post('login', [UserController::class, 'login']);
Route::post('register', [UserController::class, 'register']);

route::get('food', [FoodController::class, 'all']);