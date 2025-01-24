<?php

namespace App\Http\Middleware;


use Closure;
use App\Models\RestApiLog;
use App\Models\ApiTokenLog;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Request as FacadesRequest;

class LogRequestResponse
{
    protected $sensitiveParams = ['password'];

    public function handle(Request $request, Closure $next)
    {


        $requestId = (string) Str::uuid();
        $request->merge(['request_id' => $requestId]);
        $request->headers->set('X-Request-ID', $requestId);
        $payload = $this->maskSensitiveFields($request->all());
        $uid = null;

        $token =  FacadesRequest::bearerToken();
        if (!empty($token)) {
            $tokenData = ApiTokenLog::where(['token' => $token, 'is_active' => 1])->first();
            if (!empty($tokenData)) {
                $uid = $tokenData['uid'];
            }
        }


        $logId = DB::table('rest_api_logs')->insertGetId([
            'request_id' => $requestId,
            'uid' => $uid,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'headers' => json_encode($request->headers->all()),
            'payload' => json_encode($payload),
            'status_code' => null,
            'response' => null,
            'ip' => $request->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $start = microtime(true);

        $response = $next($request);

        $duration = round((microtime(true) - $start) * 1000, 2); // Calculate duration in milliseconds

        DB::table('rest_api_logs')->where('id', $logId)->update([
            'status_code' => $response->getStatusCode(),
            'response' => $response->getContent(),
            'duration' => $duration,
            'updated_at' => now(),
        ]);

        return $response;
    }

    private function maskSensitiveFields(array $payload): array
    {
        foreach ($this->sensitiveParams as $param) {
            if (isset($payload[$param])) {
                $payload[$param] = str_repeat('*', strlen($payload[$param]));
            }
        }
        return $payload;
    }
}
