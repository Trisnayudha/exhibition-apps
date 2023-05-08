<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\JWTAuth;

class AuthController extends Controller
{
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    public function loginOtp(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|exists:company,email',
        ]);

        $email = $request->input('email');
        $otp = rand(100000, 999999);
        // Save the OTP to the database or cache with an expiration time
        // Here we are using cache for storing the OTP
        Cache::put("otp_$email", $otp, 300); // Store the OTP for 5 minutes

        // Send OTP to the user's email
        Mail::to($email)->send(new OtpMail($otp));

        return response()->json([
            'status' => 200,
            'message' => 'OTP has been sent to your email',
            'payload' => []
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|exists:company,email',
            'otp' => 'required|integer',
        ]);

        $email = $request->input('email');
        $inputOtp = (int) $request->input('otp');

        $storedOtp = Cache::get("otp_$email");
        if ($inputOtp === $storedOtp) {
            Cache::forget("otp_$email");

            // Generate token
            $user = Company::where('email', $email)->first();
            $token = $this->jwt->fromUser($user);

            return response()->json([
                'status' => 200,
                'message' => 'OTP confirmed, login successful',
                'payload' => [
                    'user_id' => $user->id,
                    'token' => $token
                ]
            ]);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'Invalid OTP, please try again',
                'payload' => []
            ]);
        }
    }

    public function loginEmailPassword(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $credentials = $request->only('email', 'password');

        try {
            if (!$token = $this->jwt->attempt($credentials)) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Invalid credentials',
                    'payload' => []
                ], 401);
            }
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Token expired',
                'payload' => []
            ], 500);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Token invalid',
                'payload' => []
            ], 500);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
                'payload' => []
            ], 500);
        }


        // Get the authenticated user
        $user = $this->jwt->user();

        $response['status'] = 200;
        $response['message'] = 'Successfully Login';
        $response['payload'] = [
            'user_id' => $user->id,
            'token' => $token,
        ];
        return response()->json($response);
    }
}
