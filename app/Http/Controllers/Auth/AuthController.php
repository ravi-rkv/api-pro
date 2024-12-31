<?php

namespace App\Http\Controllers\Auth;


use App\Models\ApiTokenLog;
use Illuminate\Http\Request;
use App\Services\ApiResponse;
use App\Services\AuthService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
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
            'username' => [
                'required',
                'regex:/^([6-9]\d{9}|[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})$/',
            ],
            'password' => 'required|string|min:6',
        ], [
            'username.regex' => 'The username must be a valid email address or a mobile number.'
        ]);

        if ($validator->fails() == TRUE) {
            return ApiResponse::response('IRD', $validator->messages()->first(), [], 400);
        }
        return $this->authService->validateUserLoginService($request->only(['username', 'password']));
    }

    public function userRegistration(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'email' => 'required|email|max:255|unique:users,email',
                'mobile' => [
                    'required',
                    'regex:/^[6-9]\d{9}$/',
                    'unique:users,mobile'
                ],
                'gender' => 'required|in:MALE,FEMALE,OTHERS',
                'dob' => 'required|date|before: ' . now()->subYears(12)->toDateString(),
                'city' => 'required|min:3',
                'state' => 'required|min:3',
                'country' => 'required|min:3|max:100',
                'address' => 'required|min:10|max:250',
            ],
            [
                'mobile.regex' => 'The mobile number must be a valid 10-digit number.',
                'mobile.unique' => 'The mobile number has already been taken.',
            ]
        );

        if ($validator->fails() == TRUE) {
            return ApiResponse::response('IRD', $validator->messages()->first(), [], 400);
        }

        return $this->authService->validateUserRegistration($request->all());
    }

    public function verifyRegistrationOtp(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'otp' => 'required|numeric|size:6',
                'otp_reference' => 'required',
                'registration_reference' => 'required'
            ],
            [
                'size' => 'The :attribute must be exactly :size digit.',
            ]
        );

        if ($validator->fails() == TRUE) {
            return ApiResponse::response('IRD', $validator->messages()->first(), [], 400);
        }

        echo 'ok';
    }

    public function logoutUser(Request $request)
    {

        if (empty($request['uid']) || empty($request['token'])) {
            return ApiResponse::response('IRD', 'Invalid request details.', [], 400);
        }

        $logout = ApiTokenLog::where('uid', $request['uid'])->where('token', $request['token'])->update(['is_active' => 0, 'updated_at' => Carbon::now()->toDateTimeString(), 'updated_by' => $request['uid']]);

        if ($logout) {
            return ApiResponse::response('RCS', 'User logged out successfully.', [], 200);
        } else {
            return ApiResponse::response('IPE', 'Internal processing error.', [], 500);
        }
    }
}
