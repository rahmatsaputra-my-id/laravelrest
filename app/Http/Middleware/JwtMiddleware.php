<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class JwtMiddleware extends BaseMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $errorsTokenInvalid = [
            'status' => ['messages' => ['subject' => 'failed', 'system' => 'Bad Request', 'user' => 'Token is invalid'], 'code_detail' => 401],
            'meta' => ['http_status_code' => 400]
        ];
        $errorsTokenExpired = [
            'status' => ['messages' => ['subject' => 'failed', 'system' => 'Bad Request', 'user' => 'Token expired'], 'code_detail' => 401],
            'meta' => ['http_status_code' => 400]
        ];
        $errorsTokenNotFound = [
            'status' => ['messages' => ['subject' => 'failed', 'system' => 'Bad Request', 'user' => 'Token unauthorized'], 'code_detail' => 401],
            'meta' => ['http_status_code' => 400]
        ];

        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return response()->json($errorsTokenInvalid);
            } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return response()->json($errorsTokenExpired);
            } else {
                return response()->json($errorsTokenNotFound);
            }
        }
        return $next($request);
    }
}
