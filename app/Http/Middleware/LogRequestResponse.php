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

        // Log the request and response
        $this->log($request, $response, $requestId);

        return $response;
    }

    protected function log(Request $request, $response, $requestId)
    {
        RestApiLog::create([
            'request_id' => $requestId,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'headers' => json_encode($request->headers->all()),
            'payload' => $request->except(['password']), // Exclude sensitive data
            'response' => json_decode($response->getContent(), true),
            'status_code' => $response->status(),
        ]);
    }
}
