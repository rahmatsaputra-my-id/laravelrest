<?php

namespace App\Http\Controllers;

use Auth;
use JWTAuth;
use App\User;
use App\Mail\verifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);
        $meta = ['http_status' => 400];
        $errorsValidator = $validator->errors();
        $errors = ['message' => $errorsValidator,'code' => 10000];


        if($validator->fails()){
            return response()->json(compact('errors','meta'), 400);
        }

        //mail
        $verifyNumber = mt_rand(100000, 999999);
        $messageData =['message' => 'R - '.$verifyNumber.' is your account validation code.'];
        Mail::to($request->get('email'))->send(new verifyEmail($messageData));

        $usr = User::create([
            'username' => $request->get('username'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
            'noVerify' => $verifyNumber,
            'countFailed' => 0,
            'phone' => $request->get('phone'),
            'isVerified' => "false"
        ]);

//        $tokenVerify = JWTAuth::fromUser($usr);
        $status = [['message' => 'Created','code' => 10000]];
        $meta = ['http_status' => 201];

//        return response()->json(compact('status','meta','user','tokenVerify'),201);
        return response()->json(compact('status','meta','user'),201);
    }

    public function verifyEmail(Request $request)
    {
        $failedVerified = [
            'errors' => [['message' => 'Unauthorized','code' => 10001]],
            'meta' => ['http_status' => 401]
        ];
        $successVerified = [
            'status' => [['message' => 'Successfully Verify Account','code' => 10000]],
            'meta' => ['http_status' => 200]
        ];
        $errors = [
            'errors' => [['message' => 'Not Found','code' => 10004]],
            'meta' => ['http_status' => 404]
        ];
        $notFoundEmailinDb = [
            'errors' => [['message' => 'Not Found','code' => 10004]],
            'meta' => ['http_status' => 404]
        ];
        $resetVerify = [
            'errors' => [['message' => 'Maximum Failed OTP Attempts Reached, Please Wait xx Minutes and Check Email for New Code','code' => 40000]],
            'meta' => ['http_status' => 498]
        ];

        $requestEmail = $request->get('email');
        $requestNoVerify = $request->get('noVerify');
        $dataEmailRequest = User::where('email','=', $requestEmail)->first();

        if ($request !=null){
            try{
                if ($request->get('email') != null &&
                    $request->get('noVerify') != null
                ) {
                    if(!$dataEmailRequest == null){

                        $countFailedHit = $dataEmailRequest->countFailed;
                        // 3 Try failed
                        if($countFailedHit < 3)
                        {
                            $dataEmailDB = $dataEmailRequest->email;
                            $dataNoVerifyDB = $dataEmailRequest->noVerify;
                            if ($requestEmail == $dataEmailDB && $requestNoVerify == $dataNoVerifyDB){
                                $dataIdDB = $dataEmailRequest->id;
                                $findIsVerifiedWithId = User::findOrFail($dataIdDB);
                                $findIsVerifiedWithId->update([
                                    'isVerified' => "true",
                                    'noVerify' => null,
                                    'countFailed' => 0
                                    ]);
                                return response()->json($successVerified,200);
                            }
                            $dataIdDB = $dataEmailRequest->id;
                            $findIsVerifiedWithId = User::findOrFail($dataIdDB);
                            $findIsVerifiedWithId->update([
                                'countFailed' => $countFailedHit+1
                            ]);
                            return response()->json($errors, 404);
                        }
                        $dataIdDB = $dataEmailRequest->id;
                        $findIsVerifiedWithId = User::findOrFail($dataIdDB);
                        $verifyNumber = mt_rand(100000, 999999);

                        $messageData =['message' => 'R - '.$verifyNumber.' is your account validation code.'];
                        Mail::to($request->get('email'))->send(new verifyEmail($messageData));

                        $findIsVerifiedWithId->update([
                            'noVerify' => $verifyNumber,
                            'countFailed' => 0
                        ]);
                        return response()->json($resetVerify, 498);
                    }
                    return response()->json($notFoundEmailinDb, 404);
                }
                return response()->json($errors, 404);
            }catch (JWTException $e) {
                return response()->json($failedVerified, 500);
            }
        }
        return response()->json($failedVerified, 401);
    }

    public function resendVerifyEmail(Request $request){
        $failedVerified = [
            'errors' => [['message' => 'Unauthorized','code' => 10001]],
            'meta' => ['http_status' => 401]
        ];
        $notFoundEmailinDb = [
            'errors' => [['message' => 'Not Found','code' => 10004]],
            'meta' => ['http_status' => 404]
        ];
        $successVerified = [
            'status' => [['message' => 'Successfully Reset OTP','code' => 10000]],
            'meta' => ['http_status' => 200]
        ];
        $errors = [
            'errors' => [['message' => 'Not Found','code' => 10004]],
            'meta' => ['http_status' => 404]
        ];

        $requestEmail = $request->get('email');
        $dataEmailRequest = User::where('email','=', $requestEmail)->first();

        if(!$dataEmailRequest == null){
            $dataIdDB = $dataEmailRequest->id;
            $dataNoVerifyDB = $dataEmailRequest->noVerify;

            if($request !=null){
                try{
                    if($requestEmail != null){
                        if(!$dataNoVerifyDB == null){
                            $findIsVerifiedWithId = User::findOrFail($dataIdDB);
                            $verifyNumber = mt_rand(100000, 999999);
                            $findIsVerifiedWithId->update([
                                'noVerify' => $verifyNumber
                            ]);

                            $messageData =['message' => 'R - '.$verifyNumber.' is your account validation code.'];
                            Mail::to($request->get('email'))->send(new verifyEmail($messageData));
                            return response()->json($successVerified,200);
                        }
                        return response()->json($errors, 404);
                    }
                    return response()->json($notFoundEmailinDb, 404);
                }catch (JWTException $e) {
                    return response()->json($failedVerified, 500);
                }
            }
            return response()->json($failedVerified, 401);
        }
        return response()->json($errors, 404);
    }

    public function login(Request $request)
    {
        $failedLogin = [
            'errors' => [['message' => 'Unauthorized','code' => 10001]],
            'meta' => ['http_status' => 401]
        ];
        $noVerified = [
            'errors' => [['message' => 'Unverified Acoount','code' => 10001]],
            'meta' => ['http_status' => 401]
        ];
        $failedCreateToken = [
            'errors' => [['message' => 'Could Not Create Token','code' => 50000]],
            'meta' => ['http_status' => 500]
        ];
        $credentials = $request->only('username', 'password');
        $status = [['message' => 'OK','code' => 10000]];
        $meta = ['http_status' => 200];

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json($failedLogin, 401);
            }
            $checkVerified = Auth::user()->isVerified;
            if ($checkVerified != null){
                if($checkVerified == "true"){
                    return response()->json(compact('status','meta','token'));
                }
                return response()->json($noVerified, 401);
            }
            return response()->json($failedLogin, 401);
        } catch (JWTException $e) {
            return response()->json($failedCreateToken, 500);
        }
    }

    public function logout()
    {
        $dataSuccess = [
            'status' => [['message' => 'OK','code' => 10000]],
            'meta' => ['http_status' => 200],
        ];
        $token = compact('token');
        JWTAuth::parseToken()->invalidate( $token );

        return response()->json($dataSuccess,200);
    }

    public function getAuthenticatedUser()
    {
        $errors = [
            'errors' => [['message' => 'Not Found','code' => 10004]],
            'meta' => ['http_status' => 404]
        ];
        $tokenExpired = [
            'errors' => [['message' => 'Token Expired','code' => 10004]],
            'meta' => ['http_status' => 401]
        ];
        $tokenInvalid = [
            'errors' => [['message' => 'Token Invalid','code' => 10001]],
            'meta' => ['http_status' => 401]
        ];
        $tokenAbsent = [
            'errors' => [['message' => 'Token Absent','code' => 10004]],
            'meta' => ['http_status' => 401]
        ];
        $status = [['message' => 'OK','code' => 10000]];
        $meta = ['http_status' => 200];
        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json($errors, 404);
            }

        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json($tokenExpired, $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json($tokenInvalid, $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json($tokenAbsent, $e->getStatusCode());

        }
        $userData = compact('status','meta','user');
        return response()->json($userData);
    }
}
