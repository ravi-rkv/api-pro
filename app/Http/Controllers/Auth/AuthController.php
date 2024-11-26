<?php

namespace App\Http\Controllers\Auth;


use Illuminate\Http\Request;
use App\Services\ApiResponse;
use App\Services\AuthService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    private $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function validateUserLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'email' => 'nullable|email|required_without:mobile',
            // 'mobile' => 'nullable|regex:/^[6-9]\d{9}$/|required_without:email',
            'username' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL) && !preg_match('/^[6-9]\d{9}$/', $value)) {
                        $fail('The username must be a valid email address or a mobile number.');
                    }
                },
            ],
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails() == TRUE) {
            return ApiResponse::response('IRD', $validator->messages()->first(), [], 400);
        }

        return $this->authService->validateUserLogin($request->only(['username', 'password']));
    }
}
