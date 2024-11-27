<?php

namespace App\Http\Middleware;


use App\Models\RestApiLog;
use Closure;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogRequestResponse
{
    public function handle(Request $request, Closure $next)
    {

        $requestId = (string) Str::uuid();
        $request->merge(['request_id' => $requestId]);
        $response = $next($request);
        $response->headers->set('X-Request-ID', $requestId);
        $payload = $request->all();
        if (isset($payload['password'])) {
            $payload['password'] = '********'; // Mask the password
        }
        $start = microtime(true);
        $response = $next($request);
        $duration = round((microtime(true) - $start) * 1000, 2); // Calculate duration in milliseconds


        // Log the request and response
        RestApiLog::create([
            'request_id' => $requestId,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'headers' => json_encode($request->headers->all()),
            'payload' => $payload, // Exclude sensitive data
            'response' => json_decode($response->getContent(), true),
            'status_code' => $response->status(),
            'ip' => $request->ip(),
            'duration' => $duration
        ]);

        return $response;
    }
}
