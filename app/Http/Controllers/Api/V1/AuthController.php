<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\User;
use App\Notifications\ResetPassword;
use App\Services\SecurityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class AuthController extends BaseApiController
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $securityService = new SecurityService();
        $ipAddress = $request->ip();

        // Check if IP is blocked
        if ($securityService->isIpBlocked($ipAddress)) {
            throw ValidationException::withMessages([
                'email' => ['Your IP address has been temporarily blocked due to too many failed login attempts.'],
            ]);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            // Record failed login
            $securityService->recordFailedLogin($request->email, $ipAddress);
            
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if email is verified (required for production)
        if (!$user->hasVerifiedEmail()) {
            return $this->error(
                'Please verify your email address before logging in.',
                403,
                [],
                'EMAIL_NOT_VERIFIED',
                ['requires_verification' => true]
            );
        }

        // Record successful login
        $securityService->recordSuccessfulLogin($user, $ipAddress);

        // Update last login
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $ipAddress,
        ]);

        // Log activity
        \App\Models\ActivityLog::log('login', null, [], $user, 'User logged in');

        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->success([
            'user' => $user->load('roles', 'permissions'),
            'token' => $token,
        ], 'Login successful');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Assign default role (member)
        $memberRole = Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);
        $user->assignRole($memberRole);

        // Send email verification
        $user->sendEmailVerificationNotification();

        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->success([
            'user' => $user->load('roles'),
            'token' => $token,
        ], 'Registration successful. Please verify your email address.', 201);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        
        // Log activity
        \App\Models\ActivityLog::log('logout', null, [], $user, 'User logged out');

        $user->currentAccessToken()->delete();

        return $this->success(null, 'Logged out successfully');
    }

    public function user(Request $request)
    {
        return $this->success($request->user()->load('roles', 'permissions'), 'User retrieved successfully');
    }

    public function resendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->validationError(['email' => ['Email already verified']], 'Email already verified');
        }

        $request->user()->sendEmailVerificationNotification();

        return $this->success(null, 'Verification email sent');
    }

    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return $this->validationError(['hash' => ['Invalid verification link']], 'Invalid verification link');
        }

        if ($user->hasVerifiedEmail()) {
            return $this->success(null, 'Email already verified');
        }

        if ($user->markEmailAsVerified()) {
            event(new \Illuminate\Auth\Events\Verified($user));
        }

        return $this->success(null, 'Email verified successfully');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // Don't reveal if user exists (security best practice)
            return $this->success(null, 'If the email exists, a password reset link has been sent');
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        $user->notify(new ResetPassword($token));

        return $this->success(null, 'If the email exists, a password reset link has been sent');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset || !Hash::check($request->token, $passwordReset->token)) {
            return $this->validationError(['token' => ['Invalid or expired reset token']], 'Invalid or expired reset token');
        }

        // Check if token is expired (60 minutes)
        if (now()->diffInMinutes($passwordReset->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return $this->validationError(['token' => ['Reset token has expired']], 'Reset token has expired');
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $user->update(['password' => Hash::make($request->password)]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Log activity
        \App\Models\ActivityLog::log('password_reset', null, [], $user, 'Password reset via email');

        return $this->success(null, 'Password reset successfully');
    }
}
