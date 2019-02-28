<?php

namespace App\Http\Controllers;

use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use JWTAuthException;
use Hash;

class UserController extends Controller
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'avatar' => 'url',
            'password' => 'required|min:5|max:17',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $data = new User();
        $data->name = $request->input('name');
        $data->email = $request->input('email');
        $data->password = Hash::make($request->input('password'));
        $data->birthday = $request->input('birthday');
        $data->gender = $request->input('gender');
        $data->avatar = $request->input('avatar');
        $success = $data->save();
        if ($success) {
            return response()->json([
                'success' => true,
                'id' => $data->id

            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, User could not be updated'
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $token = null;
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['invalid_email_or_password'], 422);
            }
        } catch (JWTAuthException $e) {
            return response()->json(['failed_to_create_token'], 500);
        }
        return response()->json(compact('token'));
    }

    public function getUserInfo(Request $request)
    {
        $user = JWTAuth::toUser($request->token);
        return response()->json(['data' => $user]);
    }


    public function update(Request $request)
    {
        $user = JWTAuth::toUser($request->header('authorization'));
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|email',
            'avatar' => 'url',
            'password' => 'required|min:5|max:17',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $data = User::find($user->id);
        $data->name = $request->input('name');
        $data->email = $request->input('email');
        $data->password = Hash::make($request->input('password'));
        $data->birthday = $request->input('birthday');
        $data->gender = $request->input('gender');
        $data->avatar = $request->input('avatar');
        $data->updated_at = Carbon::now();
        $success = $data->save();
        if ($success) {
            return response()->json([
                'success' => true,
                'id' => $data->id

            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, User could not be updated'
            ], 500);
        }
    }

}
