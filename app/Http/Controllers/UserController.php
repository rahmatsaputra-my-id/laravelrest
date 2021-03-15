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
        $meta = ['http_status_code' => 400];
        $errorsValidator = $validator->errors();
        $status = ['developer_message' => $errorsValidator, 'system_message' => 'Conflict','code_detail' => 409];


        if($validator->fails()){
            return response()->json(compact('status','meta'), 400);
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
            'isVerified' => "FALSE"
        ]);

//        $tokenVerify = JWTAuth::fromUser($usr);
        $status = [['developer_message' => 'Successfully created '.$request->get('email').', check your email to verify.', 'system_message' => 'Created','code_detail' => 201]];
        $meta = ['http_status_code' => 200];

//        return response()->json(compact('status','meta','user','tokenVerify'),201);
        return response()->json(compact('status','meta'),200);
    }

    public function verifyEmail(Request $request)
    {
        $failedVerified = [
            'status' => [['developer_message' => 'Unauthorized', 'system_message' => 'Unauthorized','code_detail' => 401]],
            'meta' => ['http_status_code' => 400]
        ];
        $systemError = [
            'status' => [['developer_message' => 'System Error', 'system_message' => 'Internal server error','code_detail' => 401]],
            'meta' => ['http_status_code' => 500]
        ];
        $successVerified = [
            'status' => [['developer_message' => 'Successfully Verify Account','system_message' => 'OK','code_detail' => 200]],
            'meta' => ['http_status_code' => 200]
        ];
        $errors = [
            'status' => [['developer_message' => 'Not Found', 'system_message' => 'Not Found','code_detail' => 404]],
            'meta' => ['http_status_code' => 400]
        ];
        $notFoundEmailinDb = [
            'status' => [['developer_message' => 'Please recheck your email','system_message' => 'Not Found','code_detail' => 404]],
            'meta' => ['http_status_code' => 400]
        ];
        $resetVerify = [
            'status' => [['developer_message' => 'Maximum failed OTP attempts reached, Please wait xx minutes and check email for new code', 'system_message' => 'Too many request', 'code_detail' => 429]],
            'meta' => ['http_status_code' => 400]
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

                        $isVerifieduser = $dataEmailRequest->isVerified;
                        $countFailedHit = $dataEmailRequest->countFailed;
                        
                        // Not Verified User
                        if ($isVerifieduser != 'TRUE'){
                            // 2 Try failed
                            if($countFailedHit < 2)
                            {
                                $dataEmailDB = $dataEmailRequest->email;
                                $dataNoVerifyDB = $dataEmailRequest->noVerify;
                                if ($requestEmail == $dataEmailDB && $requestNoVerify == $dataNoVerifyDB){
                                    $dataIdDB = $dataEmailRequest->id;
                                    $findIsVerifiedWithId = User::findOrFail($dataIdDB);
                                    $findIsVerifiedWithId->update([
                                        'isVerified' => "TRUE",
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
                                return response()->json($errors, 400);
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
                            return response()->json($resetVerify, 400);
                        }
                        return response()->json($errors, 400);
                    }
                    return response()->json($notFoundEmailinDb, 400);
                }
                return response()->json($errors, 400);
            }catch (JWTException $e) {
                return response()->json($systemError, 500);
            }
        }
        return response()->json($failedVerified, 400);
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

//     public function forgotPasswordValidation(Request $request)
//     {
//         $validator = Validator::make($request->all(), [
//             'username' => 'required|string|max:255|unique:users',
//             'email' => 'required|string|email|max:255|unique:users',
//             'phone' => 'required|string|max:255',
//         ]);
//         $meta = ['http_status' => 400];
//         $errorsValidator = $validator->errors();
//         $errors = ['message' => $errorsValidator,'code' => 10000];


//         if($validator->fails()){
//             return response()->json(compact('errors','meta'), 400);
//         }

//         //mail
//         $verifyNumber = mt_rand(100000, 999999);
//         $messageData =['message' => 'R - '.$verifyNumber.' is your forgot password account validation code.'];
//         Mail::to($request->get('email'))->send(new verifyEmail($messageData));

//         $usr = User::create([
//             'username' => $request->get('username'),
//             'email' => $request->get('email'),
//             // 'password' => Hash::make($request->get('password')),
//             'noVerify' => $verifyNumber,
//             'countFailed' => 0,
//             'phone' => $request->get('phone'),
//             'isVerified' => "false"
//         ]);

//         $status = [['message' => 'Created','code' => 10000]];
//         $meta = ['http_status' => 201];

// //        return response()->json(compact('status','meta','user','tokenVerify'),201);
//         return response()->json(compact('status','meta'),201);
//     }

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
