<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\Manage\ManageController;
use App\Http\Controllers\TestController;

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



Route::post('/login', [AuthController::class, 'validateUserLogin']);
Route::post('/userRegistration', [AuthController::class, 'userRegistration']);
Route::post('/verifyRegistrationOtp', [AuthController::class, 'verifyRegistrationOtp']);
Route::post('/verifyLoginOtp', [AuthController::class, 'verifyLoginOtp']);
Route::post('/forgetPassword', [AuthController::class, 'forgetPassword']);
Route::post('/verifyForgetPasswordOtp', [AuthController::class, 'verifyForgetPasswordOtp']);

Route::post('/test', [TestController::class, 'test']);


/* ------------------------------ Google Login ------------------------------ */

Route::get('loginWithGoogle', [AuthController::class, 'loginWithGoogle']);
Route::get('googleAuthCallback', [AuthController::class, 'googleAuthCallback']);

Route::middleware('validateToken')->group(function () {
    Route::post('/logout', [AuthController::class, 'logoutUser']);
    Route::post('/changePassword', [AuthController::class, 'changeUserPassword']);
    Route::post('/change2FAStatus', [AuthController::class, 'change2FAStatus']);
    Route::post('/verify2FAStatusOtp', [AuthController::class, 'verify2FAStatusOtp']);


    Route::post('/getUserDetail', [UserController::class, 'getUserDetail']);
    Route::post('/updateUserDetail', [UserController::class, 'updateUserDetail']);
    Route::post('/getAllowedPages', [UserController::class, 'getAllowedPages']);
});
