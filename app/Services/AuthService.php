<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
use Firebase\JWT\JWT;
use App\Models\ApiTokenLogs;
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
        }


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

                ApiTokenLogs::where('uid', $payload['uid'])->update(['is_active' => 0]);

                $logApiToken = ApiTokenLogs::create([
                    'uid' => $payload['uid'],
                    'token' => $token,
                    'ip' => $payload['ip'],
                    'is_active' => 1,
                    'created_at' => $payload['logged_in_at'],
                    'created_by' => $payload['uid'],
                ]);

                if ($logApiToken) {
                    return ApiResponse::response('RCS', 'Logged in successfully.', ['token' => $token], 200);
                }
            }

            return ApiResponse::response('IPE', 'Internal processing error.', [], 500);
        } catch (\Exception $e) {
            return ApiResponse::response('ISE', 'Internal server error.', [], 500);
        }
    }




    private function sendLoginOtp($userDetail) {}
}
