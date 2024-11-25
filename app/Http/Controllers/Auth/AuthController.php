<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Services\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function validateUserLogin(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'email' => 'nullable|email|required_without:mobile',
            'mobile' => 'nullable|regex:/^[6-9]\d{9}$/|required_without:email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails() == TRUE) {
            return ApiResponse::error($validator->messages()->first(), [], 400);
        }
    }
}
