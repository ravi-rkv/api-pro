<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotificationConfig extends Model
{
    use HasFactory;

    public function getNotifyDataByEventCode(string $eventCode)
    {
        $data = DB::table('notification_configs as nc')
            ->join('notification_config_associations as na', 'nc.notify_id', '=', 'na.notify_id')
            ->select('nc.event_code', 'na.notify_on', 'na.op1', 'na.op2', 'na.content', 'na.notify_assoc_id', 'na.notify_id')
            ->where('nc.event_code', '=', $eventCode)
            ->where(['na.is_active' => 1, 'nc.is_active' => 1])
            ->get();
        if ($data) {
            return collect($data)->map(function ($x) {
                return (array) $x;
            })->toArray();
        }
    }
}
