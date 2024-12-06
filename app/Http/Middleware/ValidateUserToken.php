<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\ApiTokenLog;
use Illuminate\Http\Request;
use App\Services\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

class ValidateUserToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $authToken = $request->header('Authorization');
        if (!$authToken || !str_starts_with($authToken, 'Bearer ')) {
            return ApiResponse::response('IAT', 'Unauthorized: Missing or invalid token.', [], 401);
        }
        $token = substr($authToken, 7);
        $tokenData = ApiTokenLog::where(['token' => $token, 'is_active' => 1])->first();

        if (empty($tokenData)) {
            return ApiResponse::response('IAT', 'Unauthorized: Invalid or inactive token.', [], 401);
        }

        $request->merge(['token' => $token, 'uid' => $tokenData['uid']]);

        return $next($request);
    }
}
