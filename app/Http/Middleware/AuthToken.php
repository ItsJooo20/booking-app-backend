<?php
namespace App\Http\Middleware;

use Closure;
use App\Models\Users;
use Illuminate\Http\Request;

class AuthToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['status' => false, 'message' => 'Token required'], 401);
        }

        $user = Users::where('remember_token', $token)->first();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Invalid token'], 401);
        }

        $request->merge(['auth_user' => $user]);
        return $next($request);
    }
}
