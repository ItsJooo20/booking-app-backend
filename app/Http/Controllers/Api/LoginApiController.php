<?php

namespace App\Http\Controllers\Api;

use App\Models\Users;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginApiController extends Controller
{
    public function authenticate(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = Users::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Wrong email or password'
            ], 401);
        }

        $token = Str::random(60);

        $user->remember_token = $token;
        $user->save();

        Auth::login($user);

        session(['user_id' => $user->id, 'role' => $user->role]);

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'remember_token' => $token,
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $user = Users::where('remember_token', $request->bearerToken())->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Token'
            ], 401);
        }

        $user->remember_token = null;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'You are logged out'
        ]);
    }
}
