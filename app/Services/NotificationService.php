<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\SystemConfig;
use App\Models\NotificationLog;
use App\Models\NotificationConfig;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    private $notifyConfig;
    private $notifyLogs;
    private $notifySettings;

    public function __construct()
    {
        $this->notifyConfig = new NotificationConfig();
        $this->notifyLogs = new NotificationLog();
    }

    public function sendOtp($notificationDetail)
    {
        if (!empty($notificationDetail)) {

            $eventCode = @$notificationDetail['event_code'];
            $name = @$notificationDetail['name'];
            $email = @$notificationDetail['email'];
            $mobile = @$notificationDetail['mobile'];
            $userId = !empty($notificationDetail['user_id']) ? trim($notificationDetail['user_id']) : null;

            $systemConfig = SystemConfig::where('id', 1)->first()->toArray();



            if (!empty($systemConfig)) {
                $otpConfigDetail = $this->notifyConfig->getNotifyDataByEventCode($eventCode);

                if ($otpConfigDetail) {
                    $newOtpValue = mt_rand(100000, 999999);

                    foreach ($otpConfigDetail as $key => $value) {

                        if ($value['notify_on'] == 'SMS' || $value['notify_on'] == 'EMAIL') {

                            $sentOn = ($value['notify_on'] == "SMS") ? $mobile : $email;

                            $checkPreviousOtpSentOn = $this->notifyLogs->checkPreviousNotification([
                                "uid" => $userId,
                                "sent_on" => $sentOn,
                                'notify_id' => $value['notify_assoc_id'],
                                'is_valid' => 1,
                                "identifier" => isset($notificationDetail['otp_ref']) ? trim($notificationDetail['otp_ref']) : null,
                                "extra_identifier" => isset($notificationDetail['extra_identifier']) ? trim($notificationDetail['extra_identifier']) : null,
                            ]);


                            $insertNewLog = true;
                            if ($checkPreviousOtpSentOn) {
                                $lastNotifSentOn = strtotime($checkPreviousOtpSentOn['created_at']);
                                $resendAllowedUpto = strtotime("+ 10 minutes", $lastNotifSentOn);
                                $resendSameContent = (($resendAllowedUpto - time()) > 0) ? true : false;

                                if ($resendSameContent) {
                                    $insertNewLog = false;
                                    $otpValue = trim($checkPreviousOtpSentOn['identifier']); //otpval value changed.
                                } else {
                                    $otpValue = $newOtpValue;
                                    $expire_validity = $this->notifyLogs->checkNotificationValidity($checkPreviousOtpSentOn['id']);
                                    $insertNewLog = true;
                                }
                            } else {
                                // trigger new sms and email
                                $otpValue = $newOtpValue;
                                $insertNewLog = true;
                            }
                            $value['content'] = str_replace('$BRAND_NAME$', $systemConfig['name'], $value['content']);
                            $value['content'] = str_replace('$USER$', $name, $value['content']);
                            $value['content'] = str_replace('$OTP$', $newOtpValue, $value['content']);


                            if ($insertNewLog === true) {
                                $logArray = array(
                                    "notify_id" => $value['notify_id'],
                                    "notify_assoc_id" => $value['notify_assoc_id'],
                                    "uid" => $userId,
                                    "sent_on" => $sentOn,
                                    "identifier" => isset($notificationDetail['otp_ref']) ? trim($notificationDetail['otp_ref']) : null,
                                    "extra_identifier" => isset($notificationDetail['extra_identifier']) ? trim($notificationDetail['extra_identifier']) : null,
                                    "otp" => $otpValue,
                                    "content" => $value['content'],
                                    "is_valid" => 1,
                                    "created_at" => Carbon::now(),
                                );


                                // dd($logArray);

                                $insertNewOtpLog = NotificationLog::insert($logArray);
                            }

                            // $this->processNotification($value, $requestData);

                        }
                    }
                    return ['resp_code' => 'RCS', 'resp_desc' => 'OTP sent successfully.', 'data' => []];
                } else {
                    return ['resp_code' => 'ERR', 'resp_desc' => 'Something went wrong , please try again.', 'data' => []];
                }
            } else {
                return ['resp_code' => 'ERR', 'resp_desc' => 'Something went wrong , please try again.', 'data' => []];
            }
        } else {
            return ['resp_code' => 'ERR', 'resp_desc' => 'Something went wrong , please try again.', 'data' => []];
        }
    }
}
