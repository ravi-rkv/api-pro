<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\ApiTokenLog;
use Illuminate\Http\Request;
use App\Services\ApiResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Request as FacadesRequest;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {


        $token =  FacadesRequest::bearerToken();
        if (empty($token)) {
            return ApiResponse::response('IAT', 'Unauthorized: Missing or invalid token.', [], 401);
        }
        $tokenData = ApiTokenLog::where(['token' => $token, 'is_active' => 1])->first();

        if (empty($tokenData)) {
            return ApiResponse::response('IAT', 'Unauthorized: Invalid or inactive token.', [], 401);
        }
        $userDetail = getUserDataByToken($token);

        if (!empty($userDetail)) {
            if ($userDetail['role_id'] != 1) {
                return ApiResponse::response('IAT', 'Unauthorized: Service not allowed.', [], 401);
            }
        } else {
            return ApiResponse::response('IAT', 'Unauthorized: Unable to identify user.', [], 401);
        }

        return $next($request);
    }
}
