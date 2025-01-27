<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Manage\ManageController;
/* -------------------------------------------------------------------------- */
/*                                Admin Routes                                */
/* -------------------------------------------------------------------------- */

Route::post('/getUserList', [ManageController::class, 'getUserList']);
Route::post('/getUserDetailByUid', [ManageController::class, 'getUserDetailByUid']);
Route::post('/addUserDetail', [ManageController::class, 'addUserDetail']);
Route::post('/updateUserDetail', [ManageController::class, 'updateUserDetail']);
