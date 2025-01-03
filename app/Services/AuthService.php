<?php

namespace App\Services;

date_default_timezone_set('Asia/Kolkata');

use Carbon\Carbon;
use App\Models\User;
use Firebase\JWT\JWT;
use App\Models\ApiTokenLog;
use App\Models\NotificationLog;
use App\Models\TempUserRegistration;
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

        // if ($userDetail['twofa_status'] === 1) {
        //     return $this->sendOtp($userDetail);
        // };

        $userDetail = User::userDetail($userDetail['uid']);

        return $this->generateUserLoginToken($userDetail);
    }

    private function generateUserLoginToken($userDetail)
    {


        try {
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

                // ApiTokenLog::where('uid', $payload['uid'])->update(['is_active' => 0]);


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
                        'user_detail' => [
                            'uid' => $userDetail['uid'],
                            'user_name' => $userDetail['name'],
                            'email' => $userDetail['email'],
                            'mobile' => $userDetail['mobile'],
                            'role_id' => $userDetail['role_id'],
                            'role_name' => $userDetail['role_name']
                        ],
                        'logged_in_at' => $payload['logged_in_at']
                    ];
                    return ApiResponse::response('RCS', 'Logged in successfully.', $data, 200);
                }
            }

            return ApiResponse::response('IPE', 'Internal processing error.', [], 500);
        } catch (\Exception $e) {
            return ApiResponse::response('ISE', 'Internal server error.', [], 500);
        }
    }

    public function validateUserRegistration($params)
    {
        if (empty($params)) {
            ApiResponse::response('IRD', 'Invalid request detail.', [], 400);
        }

        $registrationId = generateNewRegistrationId();
        if (empty($registrationId)) {
            ApiResponse::response('SWW', 'Something went wrong , try again later', [], 500);
        }

        $saveRegistrationDetail = TempUserRegistration::create([
            'registration_id' => $registrationId,
            'name' => $params['name'],
            'email' => $params['email'],
            'mobile' => $params['mobile'],
            'gender' => $params['gender'],
            'dob' => $params['dob'],
            'city' => $params['city'],
            'state' => $params['state'],
            'country' => $params['country'],
            'address' => $params['address'],
            'password' => generateRandomString(8),
            'is_verified' => 'PENDING',
            'created_at' => Carbon::now()->toDateTimeString(),
        ]);

        if (!$saveRegistrationDetail) {
            ApiResponse::response('SWW', 'Something went wrong , try again later', [], 500);
        }

        $notificationDetail = [
            'event_code' => 'NEWREG',
            'extra_identifier' => $registrationId,
            'name' => ucfirst($params['name']),
            'email' => $params['email'],
            'mobile' => $params['mobile']
        ];

        return $this->sendOtp($notificationDetail);
    }

    private function sendOtp($notificationDetail)
    {
        if (!empty($notificationDetail)) {
            $notifyDetail = $notificationDetail;
            $notifyDetail['otp_ref'] =  !empty($notificationDetail['otp_ref']) ? $notificationDetail['otp_ref'] : date('Hmi') . rand(100001, 999999) . date('sY');

            $sendNotification = new NotificationService();
            $sendOtp =  $sendNotification->sendOtp($notifyDetail);

            if ($sendOtp['resp_code'] == 'RCS') {
                $data = ['otp_reference' => $notifyDetail['otp_ref']];

                if ($notificationDetail['event_code'] == 'NEWREG') {
                    $data['registration_reference'] = $notifyDetail['extra_identifier'];
                }

                return ApiResponse::response('RCS', 'OTP sent successfully', $data, 200);
            } else {
                return ApiResponse::response('ERR', 'Someting went wrong, try again later..!!', [], 500);
            }
        } else {
            return ApiResponse::response('IPE', 'Internal processing error. #NDNV', [], 500);
        }
    }


    public function verifyUserRegistrationOtp($params)
    {

        if (!empty($params['otp_reference']) && !empty($params['registration_reference']) && !empty($params['otp'])) {

            $otpDetail = NotificationLog::where(['identifier' => $params['otp_reference'], 'extra_identifier' => $params['registration_reference'], 'otp' => $params['otp']])->first();

            if (!empty($otpDetail)) {
                $otpDetail = $otpDetail->toArray();
                if ($otpDetail['is_valid'] == 1) {
                    if (!empty($otpDetail['created_at'])) {

                        $allowedTime = Carbon::parse($otpDetail['created_at'])->timezone('Asia/Kolkata')->addMinutes('10')->toDateTimeString(); // 10 min after otp is initiated
                        if ($allowedTime >= Carbon::now()->toDateTimeString()) {

                            $getTempRegisterDetail = TempUserRegistration::where(['registration_id' => $params['registration_reference']])->first();

                            if (!empty($getTempRegisterDetail)) {
                                $getTempRegisterDetail = $getTempRegisterDetail->toArray();
                                if ($getTempRegisterDetail['is_verified'] == 'PENDING') {

                                    $checkUserExistance = User::where('email', $getTempRegisterDetail['email'])->orWhere('mobile', $getTempRegisterDetail['mobile'])->first();
                                    if (empty($checkUserExistance)) {

                                        NotificationLog::where(['identifier' => $params['otp_reference'], 'extra_identifier' => $params['registration_reference'], 'otp' => $params['otp']])->update(['is_valid' => 0, 'updated_at' => Carbon::now()->toDateTimeString()]);


                                        $uid = generateUserId('2');
                                        if (!empty($uid)) {
                                            $registeredOn = Carbon::now()->toDateTimeString();
                                            $password = encrypt_pass($getTempRegisterDetail['password'], ['uid' => $uid, 'created_at' => $registeredOn]);

                                            if ($password) {

                                                TempUserRegistration::where(['registration_id' => $params['registration_reference']])->update(['is_verified' => 'APPROVED', 'updated_at' => Carbon::now()->toDateTimeString()]);

                                                $userData = [
                                                    'uid' => $uid,
                                                    'name' => $getTempRegisterDetail['name'],
                                                    'email' => $getTempRegisterDetail['email'],
                                                    'mobile' => $getTempRegisterDetail['mobile'],
                                                    'gender' => $getTempRegisterDetail['gender'],
                                                    'dob' => $getTempRegisterDetail['dob'],
                                                    'city' => $getTempRegisterDetail['city'],
                                                    'state' => $getTempRegisterDetail['state'],
                                                    'country' => $getTempRegisterDetail['country'],
                                                    'address' => $getTempRegisterDetail['address'],
                                                    'password' => $password,
                                                    'avatar' => 'assets/image/avatar/user.png',
                                                    'role_id' => '2',
                                                    'twofa_status' => '1',
                                                    'twofa_config' => '1',
                                                    'ref_id' => $getTempRegisterDetail['registration_id'],
                                                    'account_status' => 'ACTIVE',
                                                    'created_at' => $registeredOn,
                                                    'created_by' => $uid
                                                ];

                                                $registerUser = User::create($userData);


                                                if ($registerUser) {
                                                    return ApiResponse::response('RCS', 'User registered successfully', [], 200);
                                                } else {
                                                    return ApiResponse::response('ERR', 'Something went wrong , please try again later. #UTRUD', [], 500);
                                                }
                                            } else {
                                                return ApiResponse::response('ERR', 'Something went wrong , please try again later. #UGUPD', [], 400);
                                            }
                                        } else {
                                            return ApiResponse::response('ERR', 'Something went wrong , please try again later. #UGUID', [], 400);
                                        }
                                    } else {
                                        return ApiResponse::response('ERR', 'User already registered with requested details.', [], 400);
                                    }
                                } else {
                                    return ApiResponse::response('ERR', 'Requested user detail already processd or registered.', [], 400);
                                }
                            } else {
                                return ApiResponse::response('ERR', 'Unable to process detail, try again later. #REGNF', [], 400);
                            }
                        } else {
                            return ApiResponse::response('ERR', 'Requested OTP detail is expired', [], 400);
                        }
                    } else {
                        return ApiResponse::response('IPE', 'Something went wrong ,, please try again. #UTGET', [], 400);
                    }
                } else {
                    return ApiResponse::response('ERR', 'Requested OTP detail is not valid', [], 400);
                }
            } else {
                return ApiResponse::response('ERR', 'Invalid requested otp detail', [], 400);
            }
        } else {
            return ApiResponse::response('ERR', 'Someting went wrong, try again later..!! #RDNF', [], 400);
        }
    }
}
