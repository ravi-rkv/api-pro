<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
use Firebase\JWT\JWT;
use App\Models\ApiTokenLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class AuthService
{
    public function validateUserLoginService($params)
    {
        if (empty($params['username'])) {
            return ApiResponse::response('IRD', 'Invalid request detail .', [], 400);
        }

        $userDetail = User::where('email', $params['username'])->orWhere('mobile', $params['username'])->first();

        if (empty($userDetail)) {
            return ApiResponse::response('IRD', 'Invalid requested login detail.', [], 400);
        }

        $encryptedPassword = encrypt_pass($params['password'], $userDetail);

        if ($encryptedPassword !== $userDetail['password']) {
            return ApiResponse::response('IRD', 'Invalid username or password.', [], 400);
        }

        /* -------------------------- check if 2fa enabled -------------------------- */

        if ($userDetail['twofa_status'] === 1) {
            return $this->sendLoginOtp($userDetail);
        };

        $userDetail = User::userDetail($userDetail['uid']);


        return $this->generateUserLoginToken($userDetail);
    }

    private function generateUserLoginToken($userDetail)
    {

        try {
            // Validate request_id
            $requestId = Request::get('request_id');
            if (empty($requestId)) {
                return ApiResponse::response('IVR', 'Invalid request ID.', [], 400);
            }

            $payload = [
                'rid' => $requestId,
                'uid' => $userDetail['uid'],
                'urt' => $userDetail['role_id'],
                'ip' => Request::ip(),
                'logged_in_at' => Carbon::now()->toDateTimeString(),
            ];

            $key = config('cred.JWT_KEY');
            $token = JWT::encode($payload, $key, 'HS256');

            if ($token) {

                ApiTokenLog::where('uid', $payload['uid'])->update(['is_active' => 0]);


                $logApiToken = ApiTokenLog::create([
                    'uid' => $payload['uid'],
                    'token' => $token,
                    'ip' => $payload['ip'],
                    'is_active' => 1,
                    'created_at' => $payload['logged_in_at'],
                    'created_by' => $payload['uid'],
                ]);

                if ($logApiToken) {
                    $data = [
                        'type' => 'Bearer',
                        'token' => $token,
                        'logged_in_at' => $payload['logged_in_at'],
                        'user_detail' => [
                            'uid' => $userDetail['uid'],
                            'user_name' => $userDetail['user_name'],
                            'email' => $userDetail['email'],
                            'mobile' => $userDetail['mobile'],
                            'role_id' => $userDetail['role_id'],
                            'role_name' => $userDetail['role_name']
                        ]
                    ];
                    return ApiResponse::response('RCS', 'Logged in successfully.', $data, 200);
                }
            }

            return ApiResponse::response('IPE', 'Internal processing error.', [], 500);
        } catch (\Exception $e) {
            return ApiResponse::response('ISE', 'Internal server error.', [], 500);
        }
    }




    private function sendLoginOtp($userDetail) {}
}
