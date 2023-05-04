<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth:api')->except(['register', 'login']);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        // send otp
        $url = "https://script.google.com/macros/s/AKfycbxFNsyMXW8chGL8YhdQE1Q1yBbx5XEsq-BJeNF1a6sKoowaL_9DtcUvE_Pp0r5ootgMhQ/exec";
        $otp = mt_rand(1000, 9999);
        $response = Http::post($url, [
            'email' => $request->email,
            'subject' => 'Kode Otp ' . $request->name,
            'message' => 'Kode Otp : ' . $otp,
            'token' => '1dy09eODblmBUCTnIwiY-hbXdzCpZC3jyR4l0ZJgqQqO9L7J3zsZOobdJ'
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'otp'       => $otp,
            'password'  => Hash::make($request->password)
        ]);

        $credentials = $request->only('email', 'password');

        $token = auth()->guard('api')->attempt($credentials);
        if ($user) {
            return $this->respondWithToken($token);
        }

        return response()->json([
            'success' => false,
        ], 409);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $credentials = $request->only('email', 'password');
        $user = User::where('email', request()->email)->first();

        if (!$token = auth()->guard('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email or Password is incorrect'
            ], 401);
        } else if ($user->status == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Di tolak'
            ], 401);
        }

        return $this->respondWithToken($token);
    }

    public function getUser()
    {
        return response()->json([
            'success' => true,
            'user'    => auth()->user()
        ], 200);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . auth()->user()->id,
            'password' => 'nullable|confirmed',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $user = auth()->user();
        $user->update([
            'name'      => $request->name,
            'email'     => $request->email,
        ]);
        return response()->json([
            'success' => true,
            'user'    => auth()->user()
        ], 200);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'user' => auth()->guard('api')->user(),
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }
}
