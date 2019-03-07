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

    /**
     * Login with facebook
     *
     * return token for user
     */
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
            if (User::where('users.email', 'like', '%' . $profile['email'] . '%')->first()){
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

    /**
     * Login with google
     *
     * @return token
     */
    public function google(Request $request)
    {
        $idToken = $request->get('id_token');
        if (!$idToken) {
            return response()->json(["success" => false,
                "message" => "User login with google failed"]);
        }
        try {
            $client = new \Google_Client(['client_id' => config('services.google.client_id')]);
            $payload = $client->verifyIdToken($idToken);
            if (!$payload) {
                return response()->json(["success" => false,
                    "message" => "User login with google failed"]);
            }
            $user = User::where('users.email', 'like', '%' . $payload['email'] . '%')
                ->where('users.provided_id', 'like', '%' . $payload['sub'] . '%')
                ->where('users.social', 'like', '%' . 'google' . '%')
                ->first();
            if ($user) {
                $token = JWTAuth::fromUser($user);

                return response()->json(["success" => true,
                    "token" => $token]);
            }
            $data = new User();
            $data->name = $payload['name'];
            $data->email = $payload['email'];
            if (User::where('users.email', 'like', '%' . $payload['email'] . '%')->first()){
                return response()->json(["success" => false,
                    "message" => "This user's email has been registered"]);
            }
            $data->provided_id = $payload['sub'];
            $data->social = 'google';
            $data->avatar = $payload['picture'];
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
                    'message' => 'Sorry, User could not be create'
                ], 500);
            }
        } catch (Exception $exception) {
            return response()->json($exception->getMessage(), 500);
        }
    }


}
