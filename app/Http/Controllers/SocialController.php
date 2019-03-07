<?php

namespace App\Http\Controllers;

use App\User;
use Facebook\Facebook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use JWTAuth;

class SocialController extends Controller
{
    public function facebook(Request $request)
    {
        $facebook = $request->only('access_token');
        if (!$facebook || !isset($facebook['access_token'])) {
            return response()->json(["success" => false,
                "message" => "User login with facebook failed"]);
        }
        $fb = new Facebook([
            'app_id' => config('services.facebook.app_id'),
            'app_secret' => config('services.facebook.app_secret'),
        ]);
        try {
            $response = $fb->get('/me?fields=id,name,email,picture', $facebook['access_token']);
            $profile = $response->getGraphUser();

            if (!$profile || !isset($profile['id'])) {
                return response()->json(["success" => false,
                    "message" => "User login with facebook failed"]);
            }
            $user = User::where('users.email', 'like', '%' . $profile['email'] . '%')
                ->where('users.provided_id', 'like', '%' . $profile['id'] . '%')
                ->where('users.social', 'like', '%' . 'facebook' . '%')
                ->first();
            if ($user) {
                $token = JWTAuth::fromUser($user);

                return response()->json(["success" => true,
                    "token" => $token]);
            }
            $data = new User();
            $data->name = $profile['name'];
            $data->email = $profile['email'];
            if (User::where('users.email', 'like', '%' . $profile['email'] . '%')){
                return response()->json(["success" => false,
                    "message" => "This user's email has been registered"]);
            }
            $data->provided_id = $profile['id'];
            $data->social = 'facebook';
            $data->avatar = $profile['picture']['url'];
            $success = $data->save();
            $token = JWTAuth::fromUser($data);
            if ($success) {
                return response()->json([
                    'success' => true,
                    'token' => $token

                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Sorry, User could not be updated'
                ], 500);
            }
        } catch (Exception $exception) {
            return response()->json($exception->getMessage(), 500);
        }
    }


}
