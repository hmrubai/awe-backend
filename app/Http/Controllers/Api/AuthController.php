<?php

namespace App\Http\Controllers\Api;


use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function registerUser(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), 
            [
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required'
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'data' => $validateUser->errors()
                ], 401);
            }

            //$formData = json_decode($request->data, true);
            
            $profile_image = null;
            $profile_url = null;
            if($request->hasFile('image')){
                $image = $request->file('image');
                $time = time();
                $profile_image = "profile_image_" . $time . '.' . $image->getClientOriginalExtension();
                $destinationProfile = 'uploads/profile';
                $image->move($destinationProfile, $profile_image);
                $profile_url = $destinationProfile . '/' . $profile_image;
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'contact_no' => $request->contact_no,
                'country_id' => $request->country_id,
                'address' => $request->address,
                'institution' => $request->institution,
                'education' => $request->education,
                'user_type' => $request->user_type ? $request->user_type : "Student",
                'password' => Hash::make($request->password)
            ]);

            if($request->hasFile('image')){
                User::where('id', $user->id)->update([
                    'image' => $profile_url
                ]);
            }

            $response_user = [
                'name' => $user->name, 
                'email'=> $user->email, 
                'user_type' => $request->user_type ? $request->user_type : "Student", 
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ];

            return response()->json([
                'status' => true,
                'message' => 'Registration Successful',
                'data' => $response_user
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function loginUser(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), 
            [
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'data' => $validateUser->errors()
                ], 401);
            }

            if(!Auth::attempt($request->only(['email', 'password']))){
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                    'data' => []
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

            $response_user = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'user_type' => $user->user_type,
                'image' => $user->image,
                'address' => $user->address,
                'institution' => $user->institution,
                'education' => $user->education,
                'contact_no' => $user->contact_no,
                'updated_at' => $user->updated_at,
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ];

            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'data' => $response_user
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
                'data' => []
            ], 500);
        }
    }
}
