<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use App\Models\ApiTokenLog;
use Illuminate\Http\Request;
use App\Services\ApiResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Request as FacadesRequest;

class ValidateUserToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // $authToken = $request->header('Authorization');
        // if (!$authToken || !str_starts_with($authToken, 'Bearer ')) {
        //     return ApiResponse::response('IAT', 'Unauthorized: Missing or invalid token.', [], 401);
        // }
        // $token = substr($authToken, 7);

        $token =  FacadesRequest::bearerToken();
        if (empty($token)) {
            return ApiResponse::response('IAT', 'Unauthorized: Missing or invalid token.', [], 401);
        }

        $tokenData = ApiTokenLog::where(['token' => $token, 'is_active' => 1])->first();

        if (empty($tokenData)) {
            return ApiResponse::response('IAT', 'Unauthorized: Invalid or inactive token.', [], 401);
        }


        $userDetail = User::userDetail($tokenData['uid']);

        if (empty($userDetail)) {
            return ApiResponse::response('IAT', 'Unauthorized: Unable to identify user.', [], 401);
        }

        $request->merge(['token' => $token, 'uid' => $tokenData['uid'], 'rid' => $userDetail['role_id']]);

        return $next($request);
    }
}
