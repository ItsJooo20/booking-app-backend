<?php

namespace App\Http\Controllers;

use App\Models\Users;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function log()
    {
        return view('loginpage.login');
    }

    public function authenticate(Request $request)
    {
        $user = Users::Where('email', $request->email)->first();

        if($user && Hash::check($request->password, $user->password))
        // if($user($request->password. $user->password))
        {
            Auth::login($user);

            switch ($user->role)
            {
                    case 'admin':
                        return redirect()->route('admin.dashboard')->with('success', 'Login Succeed as Admin!');
                    case 'headmaster':
                        return redirect()->route('route ke web views dashboard HeadMaster');
                    case 'technician':
                        return redirect()->route('route ke web views dashboard Technician');
                    case 'user':
                        return redirect()->route('route ke web views dashboard User');
                    default:
                        return redirect()->route('login')->with('error', 'Invalid Role!');
            }
        }
        else 
        {
            return redirect()->route('login')->with('error', 'Wrong Email or Password!');
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login')->with('success', 'You have been logout!');
    }
}
