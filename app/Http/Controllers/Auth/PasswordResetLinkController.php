<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class PasswordResetLinkController extends Controller
{
    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json(['status' => __($status)]);
    }
}

// <?php

// namespace App\Http\Controllers\Auth;

// use App\Http\Controllers\Controller;
// use App\Models\User;
// use App\Notifications\SendOtpNotification;
// use Illuminate\Http\JsonResponse;
// use Illuminate\Http\Request;
// use Illuminate\Mail\Message;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Mail;
// use Illuminate\Support\Facades\Password;
// use Illuminate\Validation\ValidationException;

// class PasswordResetLinkController extends Controller
// {
//     /**
//      * Handle an incoming password reset link request.
//      *
//      * @throws \Illuminate\Validation\ValidationException
//      */
//     public function store(Request $request): JsonResponse
//     {
//         $request->validate([
//             'email' => ['required', 'email'],
//         ]);

//         // // Generate OTP (4-digit random number)
//         // $otp = str_pad(mt_rand(1000, 9999), 4, '0', STR_PAD_LEFT);
//         // // dd($otp);

//         // // Store the OTP in the user's record
//         // $user = User::where('email', $request->email)->first();

//         // // Store the OTP and its generation time in the user's record
//         // $user->update([
//         //     'otp' => $otp,
//         //     // 'otp_generated_at' => now(), // Store the current timestamp
//         // ]);

//         // // Send the OTP to the user via email
//         // // Mail::to($user)->send(new SendOtpNotification($user));
//         // $user->notify(new SendOtpNotification($otp));


//         // We will send the password reset link to this user. Once we have attempted
//         // to send the link, we will examine the response then see the message we
//         // need to show to the user. Finally, we'll send out a proper response.
//         $status = Password::sendResetLink(
//             $request->only('email'),
//             function (Message $message) use ($otp) {
//                 $message->line('Your OTP for password reset is: ' . $otp)
//                     ->line('This OTP will expire in 5 minutes.')
//                     // Add any other necessary information or instructions
//                     ->line('If you did not request a password reset, no further action is required.');
//             // Add any other necessary information or instructions
//             // ->action('Reset Password', url(config('app.url') . route('password.reset', $this->token, false)))
//             }
//         );

//         if ($status != Password::RESET_LINK_SENT) {
//             throw ValidationException::withMessages([
//                 'email' => [__($status)],
//             ]);
//         }

//         return response()->json(['status' => __($status)]);

//         // // Generate a random 6-digit OTP
//         // $otp = rand(100000, 999999);

//         // // Save the OTP in the database or cache
//         // // For simplicity, you can use the password_resets table with a new field 'otp'
//         // DB::table('password_resets')->updateOrInsert(
//         //     ['email' => $request->email],
//         //     ['token' => $otp]
//         // );

//         // Send OTP to the user's email
//         // Mail::to($request->email)->send(new OtpMail($otp));

//         // return response()->json(['status' => 'OTP sent successfully' ]);
//     }
// }
