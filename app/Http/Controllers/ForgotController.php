<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetMail;
use Illuminate\Support\Facades\Hash;

class ForgotController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function forgot(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|exists:company,email',
        ]);

        $email = $request->input('email');
        $token = rand(100000, 999999);
        // Save the token to the database or cache with an expiration time
        // Here we are using cache for storing the token
        Cache::put("password_reset_$email", $token, 300); // Store the token for 1 hour

        // Send reset password email
        Mail::to($email)->send(new PasswordResetMail($token));

        return response()->json([
            'status' => 200,
            'message' => 'A password reset email has been sent to your email address',
            'payload' => null
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

        $storedOtp = Cache::get("password_reset_$email");

        if ($inputOtp === $storedOtp) {
            return response()->json([
                'status' => 200,
                'message' => 'OTP verified successfully',
                'payload' => null
            ]);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'Invalid OTP, please request a new password reset',
                'payload' => null
            ]);
        }
    }

    public function resetPassword(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|exists:company,email',
            'otp' => 'required|integer',
            'password' => 'required|min:6|confirmed',
        ]);

        $email = $request->input('email');
        $inputOtp = (int) $request->input('otp');
        $newPassword = $request->input('password');

        $storedOtp = Cache::get("password_reset_$email");

        if ($inputOtp !== $storedOtp) {
            return response()->json([
                'status' => 400,
                'message' => 'Invalid OTP, please verify it first',
                'payload' => null
            ]);
        }

        Cache::forget("password_reset_$email");

        // Update user password
        $user = Company::where('email', $email)->first();
        $user->password = Hash::make($newPassword);
        $user->save();

        return response()->json([
            'status' => 200,
            'message' => 'Password reset successful',
            'payload' => null
        ]);
    }
}
