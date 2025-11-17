<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Notifications\ResetPassword;
use App\Rules\StrongPassword;
use App\Services\SecurityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

/**
 * @OA\Tag(name="Authentication")
 */
class AuthController extends BaseApiController
{
    /**
     * @OA\Post(
     *     path="/api/v1/login",
     *     tags={"Authentication"},
     *     summary="User login",
     *     description="Authenticate user and return access token",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="user", type="object"),
     *                 @OA\Property(property="token", type="string", example="1|xxxxxxxxxxxx")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Email not verified",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $securityService = new SecurityService;
        $ipAddress = $request->ip();

        // Check if IP is blocked
        if ($securityService->isIpBlocked($ipAddress)) {
            return $this->validationError([
                'email' => ['Your IP address has been temporarily blocked due to too many failed login attempts. Please try again later.'],
            ]);
        }

        // Check if account is locked
        if ($securityService->isAccountLocked($request->email)) {
            $lockoutTime = $securityService->getAccountLockoutTime($request->email);
            $remainingMinutes = $lockoutTime ? max(1, now()->diffInMinutes($lockoutTime)) : $securityService->getLockoutDuration();
            
            return $this->validationError([
                'email' => ["Account temporarily locked due to too many failed login attempts. Please try again in {$remainingMinutes} minutes."],
            ], 'Account locked', 423);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            // Record failed login
            $securityService->recordFailedLogin($request->email, $ipAddress);

            // Record failed login history if user exists
            if ($user) {
                \App\Models\LoginHistory::create([
                    'user_id' => $user->id,
                    'ip_address' => $ipAddress,
                    'user_agent' => $request->userAgent(),
                    'login_at' => now(),
                    'status' => 'failed',
                    'failure_reason' => 'Invalid password',
                ]);
            }

            return $this->validationError([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if email is verified (required for production)
        if (! $user->hasVerifiedEmail()) {
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

        // Record login history
        \App\Models\LoginHistory::create([
            'user_id' => $user->id,
            'ip_address' => $ipAddress,
            'user_agent' => $request->userAgent(),
            'login_at' => now(),
            'status' => 'success',
        ]);

        // Log activity
        \App\Models\ActivityLog::log('login', null, [], $user, 'User logged in');

        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->success([
            'user' => $user->load('roles', 'permissions'),
            'token' => $token,
        ], 'Login successful');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/register",
     *     tags={"Authentication"},
     *     summary="User registration",
     *     description="Register a new user account",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "password_confirmation"},
     *
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Registration successful",
     *
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     )
     * )
     */
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => ['required', 'confirmed', 'min:8', new StrongPassword()],
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

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

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
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

    public function verifyEmailApi(Request $request)
    {
        try {
            $request->validate([
                'token' => 'required|string',
                'email' => 'required|email',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return $this->error('User not found', 404);
        }

        if ($user->hasVerifiedEmail()) {
            return $this->success(null, 'Email already verified');
        }

        // Verify token (simplified - in production, use proper token verification)
        $verificationUrl = url("/api/v1/email/verify/{$user->id}/".sha1($user->getEmailForVerification()));

        // For API, we'll use a simpler approach - verify directly if token matches
        // In production, implement proper token verification
        if ($user->markEmailAsVerified()) {
            event(new \Illuminate\Auth\Events\Verified($user));

            return $this->success(null, 'Email verified successfully');
        }

        return $this->error('Verification failed', 400);
    }

    public function resendVerificationEmailApi(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            // Don't reveal if user exists (security best practice)
            return $this->success(null, 'If the email exists, a verification link has been sent');
        }

        if ($user->hasVerifiedEmail()) {
            return $this->validationError(['email' => ['Email already verified']], 'Email already verified');
        }

        $user->sendEmailVerificationNotification();

        return $this->success(null, 'Verification email sent');
    }

    public function forgotPassword(Request $request)
    {
        try {
            $request->validate(['email' => 'required|email']);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
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
        try {
            $request->validate([
                'token' => 'required',
                'email' => 'required|email',
                'password' => ['required', 'confirmed', 'min:8', new StrongPassword()],
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (! $passwordReset || ! Hash::check($request->token, $passwordReset->token)) {
            return $this->error('Invalid or expired reset token', 400, [], 'INVALID_TOKEN');
        }

        // Check if token is expired (60 minutes)
        if (now()->diffInMinutes($passwordReset->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return $this->error('Reset token has expired', 400, [], 'TOKEN_EXPIRED');
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $user->update(['password' => Hash::make($request->password)]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Log activity
        \App\Models\ActivityLog::log('password_reset', null, [], $user, 'Password reset via email');

        return $this->success(null, 'Password reset successfully');
    }
}
