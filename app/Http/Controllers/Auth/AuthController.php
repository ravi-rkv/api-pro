<?php

namespace App\Http\Controllers\Auth;


use Carbon\Carbon;
use App\Models\ApiTokenLog;
use Illuminate\Http\Request;
use App\Services\ApiResponse;
use App\Services\AuthService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Validator;
use App\Http\Middleware\ValidateUserToken;

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


        // var_dump($request->all());
        $validator = Validator::make(
            $request->all(),
            [
                'otp' => 'required|numeric|digits:6',
                'otp_reference' => 'required',
                'registration_reference' => 'required'
            ],
            [
                'otp.size' => 'The OTP must be exactly 6 digits.',
                'otp.required' => 'The OTP is required.',
                'otp.numeric' => 'The OTP must be numeric.',
                'otp_reference.required' => 'The OTP reference is required.',
                'registration_reference.required' => 'The registration reference is required.',
            ]
        );

        if ($validator->fails() == TRUE) {
            return ApiResponse::response('IRD', $validator->messages()->first(), [], 400);
        }

        return $this->authService->verifyUserRegistrationOtp($request->all());
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

    public function changeUserPassword(Request $request)
    {
        if (empty($request['uid']) || empty($request['token'])) {
            return ApiResponse::response('IRD', 'Invalid request details.', [], 400);
        }

        $validator = Validator::make($request->all(), [
            'new_password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[\W_]/',
            ],
            'confirm_new_password' => 'required|same:new_password'
        ], [
            'new_password.regex' => 'The password must include at least one uppercase letter, one number, and one special character.',
            'new_password.min' => 'The password must be at least 8 characters long.',
            'confirm_new_password.same' => 'The confirm password must match the new password.',
        ]);

        if ($validator->fails() == TRUE) {
            return ApiResponse::response('IRD', $validator->messages()->first(), [], 400);
        }

        return $this->authService->changeUserPassword($request->all());
    }

    public function verifyLoginOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => [
                'required',
                'regex:/^([6-9]\d{9}|[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})$/',
            ],
            'otp' => 'required|numeric|digits:6',
            'otp_reference' => 'required',
        ], [
            'otp.size' => 'The OTP must be exactly 6 digits.',
            'otp.required' => 'The OTP is required.',
            'otp.numeric' => 'The OTP must be numeric.',
            'otp_reference.required' => 'The OTP reference is required.',
            'username.regex' => 'The username must be a valid email address or a mobile number.'
        ]);

        if ($validator->fails() == TRUE) {
            return ApiResponse::response('IRD', $validator->messages()->first(), [], 400);
        }

        return $this->authService->verifyUserLoginOtp($request->all());
    }

    public function forgetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => [
                'required',
                'regex:/^([6-9]\d{9}|[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})$/',
            ]
        ], [
            'username.regex' => 'The username must be a valid email address or a mobile number.'
        ]);

        if ($validator->fails() == TRUE) {
            return ApiResponse::response('IRD', $validator->messages()->first(), [], 400);
        }

        return $this->authService->validateForgetPasswordService($request->all());
    }


    public function verifyForgetPasswordOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => [
                'required',
                'regex:/^([6-9]\d{9}|[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})$/',
            ],
            'new_password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[\W_]/',
            ],
            'confirm_new_password' => 'required|same:new_password',
            'otp' => 'required|numeric|digits:6',
            'otp_reference' => 'required',
        ], [
            'otp.size' => 'The OTP must be exactly 6 digits.',
            'otp.required' => 'The OTP is required.',
            'otp.numeric' => 'The OTP must be numeric.',
            'otp_reference.required' => 'The OTP reference is required.',
            'username.regex' => 'The username must be a valid email address or a mobile number.',
            'new_password.regex' => 'The password must include at least one uppercase letter, one number, and one special character.',
            'new_password.min' => 'The password must be at least 8 characters long.',
            'confirm_new_password.same' => 'The confirm password must match the new password.',
        ]);

        if ($validator->fails() == TRUE) {
            return ApiResponse::response('IRD', $validator->messages()->first(), [], 400);
        }

        return $this->authService->verifyUserForgetPasswordOtp($request->all());
    }

    public function loginWithGoogle()
    {
        $url = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();

        if (!empty($url)) {
            return ApiResponse::response('RCS', 'Request completed successfully', ['url' => $url], 200);
        } else {
            return ApiResponse::response('ERR', 'Something went wrong , please try again.', [], 500);
        }
    }

    public function change2FAStatus(Request $request)
    {
        if (empty($request['uid']) || empty($request['token'])) {
            return ApiResponse::response('IRD', 'Invalid request details.', [], 400);
        }

        $validator = Validator::make($request->all(), [
            'status' => [
                'required',
                'in:ENABLE,DISABLE'
            ]
        ], [
            'in' => 'Only status allowed as ENABLE & DISABLE'
        ]);
        if ($validator->fails() == TRUE) {
            return ApiResponse::response('IRD', $validator->messages()->first(), [], 400);
        }

        return $this->authService->change2FAStatus($request->all());
    }

    public function verify2FAStatusOtp(Request $request)
    {
        if (empty($request['uid']) || empty($request['token'])) {
            return ApiResponse::response('IRD', 'Invalid request details.', [], 400);
        }

        $validator = Validator::make($request->all(), [
            'status' => [
                'required',
                'in:DISABLE'
            ],
            'otp' => 'required|numeric|digits:6',
            'otp_reference' => 'required',
        ], [
            'in' => 'OTP verification is allowed only for disabling 2FA',
            'otp.size' => 'The OTP must be exactly 6 digits.',
            'otp.required' => 'The OTP is required.',
            'otp.numeric' => 'The OTP must be numeric.',
            'otp_reference.required' => 'The OTP reference is required.',
        ]);
        if ($validator->fails() == TRUE) {
            return ApiResponse::response('IRD', $validator->messages()->first(), [], 400);
        }

        return $this->authService->verify2FAStatusOtp($request->all());
    }


    public function googleAuthCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            dd($googleUser);

            // Find or create user
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                ]);
            }

            // Generate a token for the user
            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'token' => $token,
                'user' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Login failed',
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
