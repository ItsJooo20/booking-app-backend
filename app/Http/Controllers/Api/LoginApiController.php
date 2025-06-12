<?php

namespace App\Http\Controllers\Api;

use App\Models\Users;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

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

        // if (!$user->email_verified_at) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Please verify your email first. Check your inbox for verification link.',
        //         'data' => [
        //             'email' => $user->email,
        //             'verification_required' => true
        //         ]
        //     ], 403);
        // }

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

    // slash

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,technician,user,headmaster',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Users::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'phone' => $request->phone,
                'role' => $request->role ?? 'user',
                'is_active' => 1,
                'email_verification_token' => Str::random(60),
                // 'created_at' => Carbon::now(),
                // 'updated_at' => Carbon::now(),
            ]);

            // Send verification email
            $this->sendVerificationEmail($user);

            return response()->json([
                'status' => true,
                'message' => 'Registration successful. Please check your email to verify your account.',
                'data' => [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Email Verification
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'token' => 'required|string'
        ]);

        $user = Users::where('email_verification_token', $request->token)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid verification token'
            ], 400);
        }

        if ($user->email_verified_at) {
            return response()->json([
                'status' => false,
                'message' => 'Email already verified'
            ], 400);
        }

        // Check if user is active
        if (!$user->is_active) {
            return response()->json([
                'status' => false,
                'message' => 'Account is deactivated'
            ], 403);
        }

        $user->email_verified_at = Carbon::now();
        $user->email_verification_token = null;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Email verified successfully. You can now login to your account.'
        ]);
    }

    // Resend Email Verification
    public function resendVerification(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = Users::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        if ($user->email_verified_at) {
            return response()->json([
                'status' => false,
                'message' => 'Email already verified'
            ], 400);
        }

        if (!$user->is_active) {
            return response()->json([
                'status' => false,
                'message' => 'Account is deactivated'
            ], 403);
        }

        $user->email_verification_token = Str::random(60);
        $user->save();

        $this->sendVerificationEmail($user);

        return response()->json([
            'status' => true,
            'message' => 'Verification email sent successfully'
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = Users::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $token = Str::random(60);

        // Hanya update token, tidak membuat record baru
        DB::table('password_resets')->updateOrInsert(
            ['email' => $user->email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );

        // Kirim email...
        
        // DB::table('password_resets')->insert([
        //     'email' => $request->email,
        //     'token' => Hash::make($token),
        //     'created_at' => now()
        // ]);

        $emailSent = $this->sendPasswordResetEmail($user, $token);

        if (!$emailSent) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to send reset email',
                // 'error' => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => 'Password reset link sent to your email'
        ]);
    }


    // Reset Password
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $passwordReset = DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset || !Hash::check($request->token, $passwordReset->token)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid reset token'
            ], 400);
        }

        // Check if token is expired (24 hours)
        if (Carbon::parse($passwordReset->created_at)->addHours(24)->isPast()) {
            DB::table('password_resets')->where('email', $request->email)->delete();
            return response()->json([
                'status' => false,
                'message' => 'Reset token has expired'
            ], 400);
        }

        $user = Users::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        if (!$user->is_active) {
            return response()->json([
                'status' => false,
                'message' => 'Account is deactivated'
            ], 403);
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->remember_token = null; // Invalidate existing sessions
        $user->save();

        // Delete the reset token
        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Password reset successfully. Please login with your new password.'
        ]);
    }

    public function showResetForm(Request $request, $token)
    {
        $email = $request->query('email');
        
        if (!$email || !$token) {
            return view('loginpage.password-reset-error', [
                'message' => 'Invalid reset link.'
            ]);
        }

        // Verify token exists and is not expired (24 hours)
        $tokenRecord = DB::table('password_resets')
            ->where('email', $email)
            ->where('created_at', '>', Carbon::now()->subHours(24))
            ->first();

        if (!$tokenRecord) {
            return view('loginpage.password-reset-error', [
                'message' => 'This password reset link has expired or is invalid.'
            ]);
        }

        return view('loginpage.password-reset-form', [
            'token' => $token,
            'email' => $email
        ]);
    }

    public function resetPasswordForm(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required',
            'password' => ['required', 'confirmed'],
        ]);

        // Check if token exists and is valid
        $tokenRecord = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('created_at', '>', Carbon::now()->subHours(24))
            ->first();

        if (!$tokenRecord || !Hash::check($request->token, $tokenRecord->token)) {
            return back()->withErrors(['token' => 'Invalid or expired reset token.']);
        }

        // Update user password
        $user = Users::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete the token
        DB::table('password_resets')->where('email', $request->email)->delete();

        return view('loginpage.password-reset-success');
    }

    // Helper method to send verification email
    private function sendVerificationEmail($user)
    {
        $verificationUrl = url('/api/verify-email?token=' . $user->email_verification_token);
        
        try {
            Mail::send('emails.verify', ['user' => $user, 'url' => $verificationUrl], function ($message) use ($user) {
                $message->to($user->email, $user->name)
                        ->subject('Verify Your Email Address');
            });
        } catch (\Exception $e) {
            // Log the error but don't fail the registration
            // \Log::error('Failed to send verification email: ' . $e->getMessage());
        }
    }

    private function sendPasswordResetEmail($user, $token)
    {
        $reset_url = url('/reset-password?token=' . $token . '&email=' . urlencode($user->email));
        
        try {
            Mail::send('emails.password-reset', [
                'user' => $user,
                'url' => $reset_url,
                'token' => $token // Pastikan token tersedia di view
            ], function ($message) use ($user) {
                $message->to($user->email, $user->name)
                        ->subject('Reset Your Password');
            });

            // Log success
            // \Log::info('Password reset email sent to: ' . $user->email);
            
            return true;
        } catch (\Exception $e) {
            // \Log::error('Failed to send password reset email: ' . $e->getMessage());
            return false;
        }
    }
}
