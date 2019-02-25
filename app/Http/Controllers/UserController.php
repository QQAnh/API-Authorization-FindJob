<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterAuthRequest;

use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use JWTAuth;
use JWTAuthException;
use Hash;

class UserController extends Controller
{
    private $user;

    public function __construct(User $user){
        $this->user = $user;
    }

    public function register(RegisterAuthRequest $request){
        $user = $this->user->create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
            'birthday'=> $request->get('birthday'),
            'gender'=> $request->get('gender'),
        ]);
        return response()->json([
            'status'=> 200,
            'message'=> 'User created successfully',
            'data'=>$user
        ]);
    }

    public function login(Request $request){
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

    public function getUserInfo(RegisterAuthRequest $request){
        $user = JWTAuth::toUser($request->token);
        return response()->json(['result' => $user]);
    }


    public function update(Request $request)
    {
        $user = JWTAuth::toUser($request->header('authorization'));
        $data = User::find($user->id);
        $data->name = $request->input('name');
        $data->email = $request->input('email');
        $data->password = Hash::make($request->input('password'));
        $data->birthday = $request->input('birthday');
        $data->gender = $request->input('gender');
        $data->avatar = $request->input('avatar');
        $data->updated_at = Carbon::now();
        $update = $data->save();
        if ($update) {
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
