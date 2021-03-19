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
         'account_type' => 'required|string|max:255',
         'password' => 'required|string|min:6|confirmed',
      ]);
      $errorsValidator = $validator->errors();
      $failedRegister = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Conflict', 'user' => $errorsValidator], 'code_detail' => 409],
         'meta' => ['http_status_code' => 400]
      ];
      $errorsHeader = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Bad Request', 'user' => 'Missing some headers keys'], 'code_detail' => 400],
         'meta' => ['http_status_code' => 400]
      ];
      $successRegister = [
         'status' => ['messages' => ['subject' => 'success', 'system' => 'Created', 'user' => 'Successfully created ' . $request->get('email') . ', check your email to verify'], 'code_detail' => 201],
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
               'verify_code' => $verifyNumber,
               'count_failed' => 0,
               'phone' => $request->get('phone'),
               'tag' => '',
               'account_type' => $request->get('account_type'),
               'is_verified' => "FALSE",
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
         'status' => ['messages' => ['subject' => 'success', 'system' => 'OK', 'user' => 'Successfully Verify Account'], 'code_detail' => 200],
         'meta' => ['http_status_code' => 200]
      ];
      $successVerifiedReset = [
         'status' => ['messages' => ['subject' => 'success', 'system' => 'OK', 'user' => 'Successfully Verify Account'], 'code_detail' => 200],
         'meta' => ['http_status_code' => 200]
      ];
      $errors = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Not Found', 'user' => 'Not Found'], 'code_detail' => 404],
         'meta' => ['http_status_code' => 400]
      ];
      $notFoundEmailinDb = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Not Found', 'user' => 'Please recheck your email'], 'code_detail' => 404],
         'meta' => ['http_status_code' => 400]
      ];
      $resetVerify = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Too many request', 'user' => 'Maximum failed OTP attempts reached, Please wait xx minutes and check email for new code'], 'code_detail' => 429],
         'meta' => ['http_status_code' => 400]
      ];
      $errorsHeader = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Bad Request', 'user' => 'Missing some headers keys'], 'code_detail' => 404],
         'meta' => ['http_status_code' => 400]
      ];

      $requestEmail = $request->get('email');
      $requestNoVerify = $request->get('verify_code');
      $dataEmailRequest = User::where('email', '=', $requestEmail)->first();

      if ($request != null) {
         try {
            if (
               $request->get('email') != null &&
               $request->get('verify_code') != null
            ) {
               if (!$dataEmailRequest == null) {

                  $isVerifieduser = $dataEmailRequest->is_verified;
                  $countFailedHit = $dataEmailRequest->count_failed;

                  // Not Verified User
                  if ($isVerifieduser == 'FALSE') {
                     // 2 Try failed
                     if ($countFailedHit < 2) {
                        $dataEmailDB = $dataEmailRequest->email;
                        $dataNoVerifyDB = $dataEmailRequest->verify_code;
                        if ($requestEmail == $dataEmailDB && $requestNoVerify == $dataNoVerifyDB) {
                           $dataIdDB = $dataEmailRequest->id;
                           $dataTagDB = $dataEmailRequest->tag;
                           $findIsVerifiedWithId = User::findOrFail($dataIdDB);
                           if ($dataTagDB == "initial") {
                              $findIsVerifiedWithId->update([
                                 'tag' => "end",
                                 'is_verified' => "TRUE",
                                 'verify_code' => null,
                                 'count_failed' => 0
                              ]);
                              return response()->json($successVerifiedReset, 200);
                           }
                           $findIsVerifiedWithId->update([
                              'is_verified' => "TRUE",
                              'verify_code' => null,
                              'count_failed' => 0
                           ]);
                           return response()->json($successVerified, 200);
                        }
                        $dataIdDB = $dataEmailRequest->id;
                        $findIsVerifiedWithId = User::findOrFail($dataIdDB);
                        $findIsVerifiedWithId->update([
                           'count_failed' => $countFailedHit + 1
                        ]);
                        return response()->json($errors, 400);
                     }
                     $dataIdDB = $dataEmailRequest->id;
                     $findIsVerifiedWithId = User::findOrFail($dataIdDB);
                     $verifyNumber = mt_rand(100000, 999999);

                     $messageData = ['message' => 'R - ' . $verifyNumber . ' is your account validation code.'];
                     Mail::to($request->get('email'))->send(new verifyEmail($messageData));

                     $findIsVerifiedWithId->update([
                        'verify_code' => $verifyNumber,
                        'count_failed' => 0
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
         $dataNoVerifyDB = $dataEmailRequest->verify_code;

         if ($request != null) {
            try {
               if ($requestEmail != null) {
                  if (!$dataNoVerifyDB == null) {
                     $findIsVerifiedWithId = User::findOrFail($dataIdDB);
                     $verifyNumber = mt_rand(100000, 999999);
                     $findIsVerifiedWithId->update([
                        'verify_code' => $verifyNumber
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
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Unauthorized', 'user' => 'Please verify your account via email'], 'code_detail' => 401],
         'meta' => ['http_status_code' => 400]
      ];
      $failedCreateToken = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Internal Server Errors', 'user' => 'Could not create token'], 'code_detail' => 500],
         'meta' => ['http_status_code' => 500]
      ];
      $credentials = $request->only('username', 'password');
      $token = JWTAuth::attempt($credentials);
      $successLogin = [
         'status' => ['messages' => ['subject' => 'success', 'system' => 'OK', 'user' => 'Successfully login'], 'code_detail' => 200],
         'meta' => ['http_status_code' => 200],
         'token' => $token
      ];

      try {
         if (!$token) {
            return response()->json($failedToken, 401);
         }
         $checkVerified = Auth::user()->is_verified;
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

   public function forgotPasswordValidation(Request $request)
   {
      $validator = Validator::make($request->all(), [
         'password' => 'required|string|min:6|confirmed',
      ]);
      $errorsValidator = $validator->errors();
      $errorsHeader = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Bad Request', 'user' => 'Missing some headers keys'], 'code_detail' => 400],
         'meta' => ['http_status_code' => 400]
      ];
      $failedValidation = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Bad Request', 'user' => $errorsValidator], 'code_detail' => 400],
         'meta' => ['http_status_code' => 400]
      ];
      $successVerifiedInitial = [
         'status' => ['messages' => ['subject' => 'success', 'system' => 'OK', 'user' => 'Succes initial reset password ' . $request->get('email') . ', check your email to verify'], 'code_detail' => 200],
         'meta' => ['http_status_code' => 200],
         'data' => ['tag' => 'initial']
      ];
      $successVerifiedEnd = [
         'status' => ['messages' => ['subject' => 'success', 'system' => 'OK', 'user' => 'Successfully reset password'], 'code_detail' => 200],
         'meta' => ['http_status_code' => 200],
         'data' => ['tag' => 'end']
      ];
      $systemError = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Internal server error', 'user' => 'System Error'], 'code_detail' => 500],
         'meta' => ['http_status_code' => 500]
      ];
      $failedVerified = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Unauthorized', 'user' => 'Unauthorized'], 'code_detail' => 401],
         'meta' => ['http_status_code' => 400]
      ];
      $requestPasswordNotMatch = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Bad Request', 'user' => $errorsValidator], 'code_detail' => 400],
         'meta' => ['http_status_code' => 400]
      ];
      $errors = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Not Found', 'user' => 'Not Found'], 'code_detail' => 404],
         'meta' => ['http_status_code' => 400]
      ];
      $notFoundEmailinDb = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Not Found', 'user' => 'Please recheck your email'], 'code_detail' => 404],
         'meta' => ['http_status_code' => 400]
      ];

      $requestEmail = $request->get('email');
      $requestPassword = $request->get('password');
      $requestPasswordConfirmation = $request->get('password_confirmation');
      $dataEmailRequest = User::where('email', '=', $requestEmail)->first();

      if ($request != null) {
         try {
            if ($requestEmail != null || $requestEmail != "") {
               if (!$dataEmailRequest == null) {

                  $tagUser = $dataEmailRequest->tag;
                  $dataEmailDB = $dataEmailRequest->email;

                  if ($tagUser == '') {
                     if ($requestEmail == $dataEmailDB) {
                        $dataIdUser = $dataEmailRequest->id;
                        $findIsVerifiedWithId = User::findOrFail($dataIdUser);
                        $verifyNumber = mt_rand(100000, 999999);

                        $messageData = ['message' => 'R - ' . $verifyNumber . ' is your account validation code.'];
                        Mail::to($request->get('email'))->send(new verifyEmail($messageData));

                        $findIsVerifiedWithId->update([
                           'verify_code' => $verifyNumber,
                           'is_verified' => 'FALSE',
                           'count_failed' => 0,
                           'tag' => 'initial'
                        ]);
                        return response()->json($successVerifiedInitial, 200);
                     }
                     return response()->json($notFoundEmailinDb, 400);
                  }
                  if ($tagUser == "end") {
                     if ($requestPassword != null  || $requestPasswordConfirmation != null) {
                        if ($requestPassword != '' || $requestPasswordConfirmation != '') {
                           if ($validator->fails()) {
                              return response()->json($requestPasswordNotMatch, 400);
                           }
                           $dataIdDB = $dataEmailRequest->id;
                           $findIsVerifiedWithId = User::findOrFail($dataIdDB);
                           $findIsVerifiedWithId->update([
                              'is_verified' => 'TRUE',
                              'verify_code' => null,
                              'count_failed' => 0,
                              'tag' => '',
                              'password' => Hash::make($requestPassword),
                           ]);
                           return response()->json($successVerifiedEnd, 200);
                        }
                        return response()->json($failedValidation, 400);
                     }
                     return response()->json($failedValidation, 400);
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

   public function logout()
   {
      $dataSuccess = [
         'status' => ['messages' => ['subject' => 'success', 'system' => 'OK', 'user' => 'Successfully logout'], 'code_detail' => 200],
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
      $user = JWTAuth::parseToken()->authenticate();
      $successGetUser = [
         'status' => ['messages' => ['subject' => 'success', 'system' => 'OK', 'user' => 'Successfully get users'], 'code_detail' => 200],
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
