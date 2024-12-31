<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    use HasFactory;

    public function checkPreviousNotification($requestData)
    {

        $data = NotificationLog::where($requestData)->first();

        if ($data) {
            return $data->toArray();
        }
    }
    public function checkNotificationValidity($id)
    {

        $data = NotificationLog::where('id', $id)->where('is_valid', '0')->first();
        if ($data) {
            return $data->toArray();
        }
    }

    public function checkPreviousMessageByCode($user_id, $event_code, $otp, $identifire = null)
    {
        $data = NotificationLog::from('notification_logs as nl')
            ->join('notification_configs as nc')
            ->select('nl.created_at', 'nl.notify_id')
            ->where(['nl.is_valid' => 1, 'nc.is_active' => 1, 'nc.event_code' => $event_code, 'nl.user_id' => $user_id, 'nl.identifier' => $otp]);
        if ($identifire) {
            $data->where(['nl.extra_identifier' => $otp]);
        }
    }
}
