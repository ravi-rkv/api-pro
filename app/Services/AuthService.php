<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;

class AuthService
{
    public function validateUserLogin($params)
    {
        if (empty($params['username'])) {
            return ApiResponse::response('IRD', 'Invalid request detail .', [], 400);
        }

        $userDetail = null;

        if (filter_var($params['username'], FILTER_VALIDATE_EMAIL)) {
            $userDetail = User::where('email', $params['username'])->first();
        } else if (preg_match('/^[6-9]\d{9}$/', $params['username'])) {
            $userDetail = User::where('mobile', $params['username'])->first();
        }

        if (empty($userDetail)) {
            return ApiResponse::response('IRD', 'Invalid requested login detail.', [], 400);
        }

        // $encryptedPassword =

        // if()


        dd($userDetail);
    }
}
