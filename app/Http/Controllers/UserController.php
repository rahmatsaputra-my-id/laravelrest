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
    $errorsValidator = $validator->errors();
    $failedRegister = [
      'status' => ['messages' => ['subject' => 'failed', 'system' => 'Conflict', 'user' => $errorsValidator], 'code_detail' => 409],
      'meta' => ['http_status_code' => 400]
    ];
    $errorsHeader = [
      'status' => ['messages' => ['subject' => 'failed', 'system' => 'Bad Request', 'user' => 'Missing some headers keys.'], 'code_detail' => 400],
      'meta' => ['http_status_code' => 400]
    ];
    $successRegister = [
      'status' => ['messages' => ['subject' => 'success', 'system' => 'Created', 'user' => 'Successfully created ' . $request->get('email') . ', check your email to verify.'], 'code_detail' => 201],
      'user',
      'meta' => ['http_status_code' => 200]
    ];
    $systemError = [
      'status' => ['messages' => ['subject' => 'failed', 'system' => 'Internal server error', 'user' => 'System Error'], 'code_detail' => 500],
      'meta' => ['http_status_code' => 500]
    ];

    if ($request != null) {
      try {
        if ($validator->fails()) {
          return response()->json($failedRegister, 400);
        }

        // Mail
        $verifyNumber = mt_rand(100000, 999999);
        $messageData = ['message' => 'R - ' . $verifyNumber . ' is your account validation code.'];
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

        // $tokenVerify = JWTAuth::fromUser($usr);
        return response()->json($successRegister, 200);
      } catch (JWTException $e) {
        return response()->json($systemError, 500);
      }
    }
    return response()->json($errorsHeader, 400);
  }

  public function verifyEmail(Request $request)
  {
    $failedVerified = [
      'status' => ['messages' => ['subject' => 'failed', 'system' => 'Unauthorized', 'user' => 'Unauthorized'], 'code_detail' => 401],
      'meta' => ['http_status_code' => 400]
    ];
    $systemError = [
      'status' => ['messages' => ['subject' => 'failed', 'system' => 'Internal server error', 'user' => 'System Error'], 'code_detail' => 500],
      'meta' => ['http_status_code' => 500]
    ];
    $successVerified = [
      'status' => ['messages' => ['subject' => 'success', 'system' => 'OK', 'user' => 'Successfully Verify Account.'], 'code_detail' => 200],
      'meta' => ['http_status_code' => 200]
    ];
    $errors = [
      'status' => ['messages' => ['subject' => 'failed', 'system' => 'Not Found', 'user' => 'Not Found'], 'code_detail' => 404],
      'meta' => ['http_status_code' => 400]
    ];
    $notFoundEmailinDb = [
      'status' => ['messages' => ['subject' => 'failed', 'system' => 'Not Found', 'user' => 'Please recheck your email.'], 'code_detail' => 404],
      'meta' => ['http_status_code' => 400]
    ];
    $resetVerify = [
      'status' => ['messages' => ['subject' => 'failed', 'system' => 'Too many request', 'user' => 'Maximum failed OTP attempts reached, Please wait xx minutes and check email for new code.'], 'code_detail' => 429],
      'meta' => ['http_status_code' => 400]
    ];
    $errorsHeader = [
      'status' => ['messages' => ['subject' => 'failed', 'system' => 'Bad Request', 'user' => 'Missing some headers keys.'], 'code_detail' => 404],
      'meta' => ['http_status_code' => 400]
    ];

    $requestEmail = $request->get('email');
    $requestNoVerify = $request->get('noVerify');
    $dataEmailRequest = User::where('email', '=', $requestEmail)->first();

    if ($request != null) {
      try {
        if (
          $request->get('email') != null &&
          $request->get('noVerify') != null
        ) {
          if (!$dataEmailRequest == null) {

            $isVerifieduser = $dataEmailRequest->isVerified;
            $countFailedHit = $dataEmailRequest->countFailed;

            // Not Verified User
            if ($isVerifieduser != 'TRUE') {
              // 2 Try failed
              if ($countFailedHit < 2) {
                $dataEmailDB = $dataEmailRequest->email;
                $dataNoVerifyDB = $dataEmailRequest->noVerify;
                if ($requestEmail == $dataEmailDB && $requestNoVerify == $dataNoVerifyDB) {
                  $dataIdDB = $dataEmailRequest->id;
                  $findIsVerifiedWithId = User::findOrFail($dataIdDB);
                  $findIsVerifiedWithId->update([
                    'isVerified' => "TRUE",
                    'noVerify' => null,
                    'countFailed' => 0
                  ]);
                  return response()->json($successVerified, 200);
                }
                $dataIdDB = $dataEmailRequest->id;
                $findIsVerifiedWithId = User::findOrFail($dataIdDB);
                $findIsVerifiedWithId->update([
                  'countFailed' => $countFailedHit + 1
                ]);
                return response()->json($errors, 400);
              }
              $dataIdDB = $dataEmailRequest->id;
              $findIsVerifiedWithId = User::findOrFail($dataIdDB);
              $verifyNumber = mt_rand(100000, 999999);

              $messageData = ['message' => 'R - ' . $verifyNumber . ' is your account validation code.'];
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
        return response()->json($errorsHeader, 400);
      } catch (JWTException $e) {
        return response()->json($systemError, 500);
      }
    }
    return response()->json($failedVerified, 400);
  }

  public function resendVerifyEmail(Request $request)
  {
    $failedVerified = [
      'status' => ['messages' => ['subject' => 'failed', 'system' => 'Unauthorized', 'user' => 'Unauthorized'], 'code_detail' => 401],
      'meta' => ['http_status_code' => 400]
    ];
    $systemError = [
      'status' => ['messages' => ['subject' => 'failed', 'system' => 'Internal server error', 'user' => 'System Error'], 'code_detail' => 500],
      'meta' => ['http_status_code' => 500]
    ];
    $notFoundEmailinDb = [
      'status' => ['messages' => ['subject' => 'failed', 'system' => 'Not Found', 'user' => 'Not Found'], 'code_detail' => 401],
      'meta' => ['http_status_code' => 400]
    ];
    $successVerified = [
      'status' => ['messages' => ['subject' => 'success', 'system' => 'OK', 'user' => 'Successfully Reset OTP'], 'code_detail' => 200],
      'meta' => ['http_status_code' => 200]
    ];
    $errors = [
      'status' => ['messages' => ['subject' => 'failed', 'system' => 'Not Found', 'user' => 'Not Found'], 'code_detail' => 404],
      'meta' => ['http_status_code' => 400]
    ];
    $errorsHeader = [
      'status' => ['messages' => ['subject' => 'failed', 'system' => 'Bad Request', 'user' => 'Missing some headers keys'], 'code_detail' => 400],
      'meta' => ['http_status_code' => 400]
    ];

    $requestEmail = $request->get('email');
    $dataEmailRequest = User::where('email', '=', $requestEmail)->first();

    if (!$dataEmailRequest == null) {
      $dataIdDB = $dataEmailRequest->id;
      $dataNoVerifyDB = $dataEmailRequest->noVerify;

      if ($request != null) {
        try {
          if ($requestEmail != null) {
            if (!$dataNoVerifyDB == null) {
              $findIsVerifiedWithId = User::findOrFail($dataIdDB);
              $verifyNumber = mt_rand(100000, 999999);
              $findIsVerifiedWithId->update([
                'noVerify' => $verifyNumber
              ]);

              $messageData = ['message' => 'R - ' . $verifyNumber . ' is your account validation code.'];
              Mail::to($request->get('email'))->send(new verifyEmail($messageData));
              return response()->json($successVerified, 200);
            }
            return response()->json($errors, 400);
          }
          return response()->json($notFoundEmailinDb, 400);
        } catch (JWTException $e) {
          return response()->json($systemError, 500);
        }
      }
      return response()->json($failedVerified, 400);
    }
    return response()->json($errorsHeader, 400);
  }

  public function login(Request $request)
  {
    $failedLogin = [
      'status' => ['messages' => ['subject' => 'failed', 'system' => 'Unauthorized', 'user' => 'Invalid password'], 'code_detail' => 401],
      'meta' => ['http_status_code' => 400]
    ];
    $failedToken = [
      'status' => ['messages' => ['subject' => 'failed', 'system' => 'Unauthorized', 'user' => 'Invalid email or password'], 'code_detail' => 401],
      'meta' => ['http_status_code' => 400]
    ];
    $noVerified = [
      'status' => ['messages' => ['subject' => 'failed', 'system' => 'Unauthorized', 'user' => 'Verify your account via email.'], 'code_detail' => 401],
      'meta' => ['http_status_code' => 400]
    ];
    $failedCreateToken = [
      'status' => ['messages' => ['subject' => 'failed', 'system' => 'Internal Server Errors', 'user' => 'Could not create token.'], 'code_detail' => 500],
      'meta' => ['http_status_code' => 500]
    ];
    $credentials = $request->only('username', 'password');
    $token = JWTAuth::attempt($credentials);
    $successLogin = [
      'status' => ['messages' => ['subject' => 'success', 'system' => 'OK', 'user' => 'Successfully login.'], 'code_detail' => 200],
      'meta' => ['http_status_code' => 200],
      'token' => $token
    ];

    try {
      if (!$token) {
        return response()->json($failedToken, 401);
      }
      $checkVerified = Auth::user()->isVerified;
      if ($checkVerified != null) {
        if ($checkVerified == "TRUE") {
          return response()->json($successLogin, 200);
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
      'status' => ['messages' => ['subject' => 'success', 'system' => 'OK', 'user' => 'Successfully logout.'], 'code_detail' => 200],
      'meta' => ['http_status_code' => 200],
    ];
    $token = JWTAuth::parseToken()->authenticate();
    JWTAuth::parseToken()->invalidate($token);

    return response()->json($dataSuccess, 200);
  }

  public function getAuthenticatedUser()
  {
    $errors = [
      'status' => ['messages' => ['subject' => 'failed', 'system' => 'Not Found', 'user' => 'Not Found'], 'code_detail' => 404],
      'meta' => ['http_status_code' => 400]
    ];
    $tokenExpired = [
      'status' => ['messages' => ['subject' => 'failed', 'system' => 'Unauthorized', 'user' => 'Token Expired'], 'code_detail' => 401],
      'meta' => ['http_status_code' => 400]
    ];
    $tokenInvalid = [
      'status' => ['messages' => ['subject' => 'failed', 'system' => 'Unauthorized', 'user' => 'Token Invalid'], 'code_detail' => 401],
      'meta' => ['http_status_code' => 400]
    ];
    $tokenAbsent = [
      'status' => ['messages' => ['subject' => 'failed', 'system' => 'Unauthorized', 'user' => 'Token Absent'], 'code_detail' => 401],
      'meta' => ['http_status_code' => 400]
    ];
    $user = JWTAuth::parseToken()->authenticate();
    $successGetUser = [
      'status' => ['messages' => ['subject' => 'success', 'system' => 'OK', 'user' => 'Successfully get users.'], 'code_detail' => 200],
      'meta' => ['http_status_code' => 200],
      'user' => $user
    ];

    try {
      if (!$user) {
        return response()->json($errors, 404);
      }
    } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
      return response()->json($tokenExpired, $e->getStatusCode());
    } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
      return response()->json($tokenInvalid, $e->getStatusCode());
    } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
      return response()->json($tokenAbsent, $e->getStatusCode());
    }
    return response()->json($successGetUser);
  }
}
