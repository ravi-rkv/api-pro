<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Request;

class ApiResponse
{
    public static function response($resp_code = 'IPE', $resp_desc = 'Internal Processing Error', $data = [], $status_code = 500)
    {
        $requestId = Request::get('request_id');

        return response()->json([
            'resp_code' => $resp_code,
            'resp_desc' => $resp_desc,
            'request_id' => $requestId,
            'data' => $data,
            // 'timestamp' => Carbon::now()->toDateTimeString(),
        ], $status_code);
    }
}
