<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [AuthController::class, 'validateUserLogin']);
Route::post('/userRegistration', [AuthController::class, 'userRegistration']);
Route::post('/verifyRegistrationOtp', [AuthController::class, 'verifyRegistrationOtp']);

Route::middleware('validateToken')->group(function () {
    Route::post('/logout', [AuthController::class, 'logoutUser']);
    Route::post('/changePassword', [AuthController::class, 'changeUserPassword']);
});
