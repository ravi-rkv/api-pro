<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Manage\ManageController;
/* -------------------------------------------------------------------------- */
/*                                Admin Routes                                */
/* -------------------------------------------------------------------------- */

Route::post('/getUserList', [ManageController::class, 'getUserList']);
Route::post('/getUserDetailByUid', [ManageController::class, 'getUserDetailByUid']);
