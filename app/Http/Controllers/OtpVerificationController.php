<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Password;

class OtpVerificationController extends Controller
{
    public function verifyOtp(Request $request)
    {
        // $request->validate([
        //     'otp' => 'required|string',
        // ]);

        // Your OTP verification logic here
        // Compare the provided OTP with the one generated and stored during the forgot password process
        // If the OTP is valid, generate a new token for password reset and return it to the frontend

        // Example: Assuming you have stored the OTP in the users table
        // $user = Auth::user();

        // if ($user->otp === $request->otp) {
        //     // Generate a new token for password reset
        //     $token = $user->createToken('password-reset')->plainTextToken;

        //     // Clear the OTP in the database
        //     $user->update(['otp' => null]);

        //     return response()->json(['status' => 'success', 'token' => $token]);
        // }

        // return response()->json(['status' => 'error', 'message' => 'Invalid OTP'], 422);

        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|digits:4',
        ]);

        $credentials = $request->only('email', 'otp');
    
        $user = User::where('email', $request->email)->first();
    
        if ($user->otp !== $request->otp) {
            return response()->json(['status' => 'error', 'message' => 'Invalid OTP'], 422);
        }
    
        // Generate a token for password reset
        // $token = Password::broker()->createToken($user);
        $token = $user->createToken('password-reset')->plainTextToken;
    
        // Associate the token with the user's record
        $user->update(['password_reset_token' => $token]);
    
        return response()->json(['status' => 'success', 'token' => $token]);
    }
}