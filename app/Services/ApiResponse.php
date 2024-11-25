<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Request;

class ApiResponse
{
    public static function generate($status, $message = null, $data = [], $status_code = 200)
    {
        $requestId = Request::get('request_id');

        return response()->json([
            'status' => $status,
            'request_id' => $requestId,
            'message' => $message,
            'data' => $data,
            'timestamp' => Carbon::now()->toDateTimeString(),
        ], $status_code);
    }

    public static function success($message, $data = [], $status_code = 200)
    {
        return self::generate(true, $message, $data, $status_code);
    }

    public static function error($message, $data = [], $status_code = 400)
    {
        return self::generate(false, $message, $data, $status_code);
    }
}
