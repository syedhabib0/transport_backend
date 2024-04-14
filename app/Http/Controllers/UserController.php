<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function webLogin(Request $request){
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = $this->user->with('profile')->where('email',$request->email)->first();
        $driver = Driver::where('user_id', $user->id)->first();
        if ($driver) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        if(!$user || !Hash::check($request->password,$user->password)){
            return response()->json([
                'message' => 'Invalid Credentials'
            ],401);
        }
        $user->fcm_token = $request->fcm_token;
        $user->save();
        $token = $user->createToken($user->first_name.'-AuthToken')->plainTextToken;
        return response()->json([
            'user' => $user,
            'access_token' => $token,
        ]);
    }

    public function login(Request $request){
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'fcm_token' => 'nullable',
        ]);

        $user = $this->user->with('profile')->where('email',$request->email)->first();
        if(!$user || !Hash::check($request->password,$user->password)){
            return response()->json([
                'message' => 'Invalid Credentials'
            ],401);
        }
        $user->fcm_token = $request->fcm_token;
        $user->save();
        $token = $user->createToken($user->first_name.'-AuthToken')->plainTextToken;
        return response()->json([
            'user' => $user,
            'access_token' => $token,
        ]);
    }

    public function logout(){
        auth()->user()->tokens()->delete();
    
        return response()->json([
          "message"=>"logged out"
        ]);
    }

    public function updateDriverProfile(Request $request)
    {
        $request->validate([
            'first_name' => 'nullable',
            'last_name' => 'nullable',
            'email' => 'nullable|email',
            'phone_number' => 'nullable',
        ]);
        try {
            $userData = $request->only([
                'first_name',
                'last_name',
                'email'
            ]);
            $user = $this->user->where('id', getCurrentUser()->id)->first();
            if(isset($request->phone_number)) {
                $user->profile()->updateOrCreate(
                    ['user_id' => $user->id],
                    ['phone' => $request->phone_number]);
            }
            $user->update($userData);
            
            return successResponse('Profile updated successfully.');
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function updateDriverImage(Request $request)
    {
        $request->validate([
            'image' => 'required|mimes:jpeg,jpg,png|max:2048'
        ]);
        try {
            $user = getCurrentUser();
            $image = $request->file('image');
            $filename = $user->id . '_' . time() . '.' . $image->getClientOriginalExtension();

            Storage::disk('public')->putFileAs('storage/driver_images', $image, $filename);
            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                ['profile_photo' => 'driver_images/' . $filename]);
            
            $userData =  $user->load('profile');
            return successResponse('Image updated successfully.', $userData);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function updateDriverPassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:6',
            'confirm_password' => 'required|same:new_password',
        ]);

        try {
            $user = $this->user->where('id', getCurrentUser()->id)->first();

            $oldPassword = $request->input('old_password');

            if (!Hash::check($oldPassword, $user->password)) {
                return errorResponse('Old password is incorrect.', 400);
            }

            $newPassword = $request->input('new_password');
            $user->password = Hash::make($newPassword);
            $user->password_updated_at = now();
            $user->save();
            return successResponse('Password updated successfully.');
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return errorResponse($validator->errors(), 422);
        }
 
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if($status === Password::RESET_LINK_SENT) {
            return successResponse('Password reset link has been sent');
        }

        return errorResponse('Unable to sent the link');
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return errorResponse($validator->errors(), 422);
        }
     
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));
     
                $user->save();
     
                event(new PasswordReset($user));
            }
        );

        if($status === Password::PASSWORD_RESET) {
            return successResponse('Password updated successfully!');
        }

        return errorResponse('Unable to update password');
    }

    public function registration(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => ['required', 'email', 'unique:'.User::class],
            'password' => 'required',
            'confirm_password' => 'required|same:password'
        ]);

        try {
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $role = Role::where('name', 'dispatcher')->first();
            $user->assignRole($role);
            return successResponse('User registered successfully.', $user);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), $e->getCode());
        }
    }
}
