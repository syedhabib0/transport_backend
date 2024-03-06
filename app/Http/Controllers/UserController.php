<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
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
}
