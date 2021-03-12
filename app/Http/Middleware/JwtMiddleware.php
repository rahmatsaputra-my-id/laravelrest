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
            'errors' => [['message' => 'Token is Invalid','code' => 10001]],
            'meta' => ['http_status' => 401]    
        ];

        $errorsTokenExpired = [
            'errors' => [['message' => 'Token Expired','code' => 10001]],
            'meta' => ['http_status' => 401]    
        ];

        $errorsTokenNotFound = [
            'errors' => [['message' => 'Unauthorized','code' => 10001]],
            'meta' => ['http_status' => 401]    
        ];

        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json($errorsTokenInvalid);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return response()->json($errorsTokenExpired);
            }else{
                return response()->json($errorsTokenNotFound);
            }
        }
        return $next($request);
    }
}