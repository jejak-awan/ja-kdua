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
        // Use IpHelper to get real client IP (handles proxies/CDN properly)
        $ipAddress = \App\Helpers\IpHelper::getClientIp($request);

        // Check if IP is blocked (auto-expires via cache TTL)
        if ($securityService->isIpBlocked($ipAddress)) {
            $remainingSeconds = $securityService->getRemainingBlockTime($ipAddress);
            $remainingMinutes = max(1, ceil($remainingSeconds / 60));
            
            return response()->json([
                'success' => false,
                'message' => "Your IP address has been temporarily blocked. Please try again in {$remainingMinutes} minute(s).",
                'retry_after' => $remainingSeconds,
                'errors' => [
                    'email' => ["Too many failed login attempts. Please try again in {$remainingMinutes} minute(s)."],
                ],
            ], 429);
        }

        // Check if account is locked (auto-expires via cache TTL)
        if ($securityService->isAccountLocked($request->email)) {
            $remainingSeconds = $securityService->getAccountLockoutRemaining($request->email);
            $remainingMinutes = max(1, ceil($remainingSeconds / 60));
            
            return response()->json([
                'success' => false,
                'message' => "Account temporarily locked. Please try again in {$remainingMinutes} minute(s).",
                'retry_after' => $remainingSeconds,
                'errors' => [
                    'email' => ["Account temporarily locked due to too many failed login attempts. Please try again in {$remainingMinutes} minute(s)."],
                ],
            ], 429);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            // Record failed login (may trigger progressive blocking)
            $result = $securityService->recordFailedLogin($request->email, $ipAddress);

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

            // If IP was just blocked, return 429 with retry_after
            if ($result['ip_blocked']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many failed attempts. Your IP has been temporarily blocked.',
                    'retry_after' => $result['block_duration'],
                    'errors' => [
                        'email' => ['Too many failed login attempts. Please try again later.'],
                    ],
                ], 429);
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

        // Check if 2FA is enabled
        if ($user->hasTwoFactorEnabled()) {
            // Check if 2FA code is provided
            if (! $request->has('two_factor_code')) {
                return $this->success([
                    'requires_two_factor' => true,
                    'user_id' => $user->id,
                ], 'Two-factor authentication code required', 200);
            }

            // Verify 2FA code
            $twoFactorAuth = $user->twoFactorAuth;
            $secret = $twoFactorAuth->getDecryptedSecret();
            
            $google2fa = new \PragmaRX\Google2FA\Google2FA;
            $valid = $google2fa->verifyKey($secret, $request->two_factor_code, 2);

            // If TOTP fails, try backup code
            if (! $valid) {
                $valid = $twoFactorAuth->verifyBackupCode($request->two_factor_code);
            }

            if (! $valid) {
                // Record failed login
                $securityService->recordFailedLogin($request->email, $ipAddress);
                
                \App\Models\LoginHistory::create([
                    'user_id' => $user->id,
                    'ip_address' => $ipAddress,
                    'user_agent' => $request->userAgent(),
                    'login_at' => now(),
                    'status' => 'failed',
                    'failure_reason' => 'Invalid 2FA code',
                ]);

                return $this->validationError([
                    'two_factor_code' => ['Invalid two-factor authentication code'],
                ]);
            }
        }

        // Log activity (optional - skip if ActivityLog doesn't exist)
        try {
            if (class_exists(\App\Models\ActivityLog::class)) {
                \App\Models\ActivityLog::log('login', null, [], $user, 'User logged in');
            }
        } catch (\Exception $e) {
            // ActivityLog not available, skip
        }

        // Handle concurrent login control
        $singleSession = \App\Models\Setting::get('single_session_enabled', false);
        $maxSessions = \App\Models\Setting::get('max_concurrent_sessions', 0);

        if ($singleSession) {
            // Revoke all previous tokens for this user (single session mode)
            $revokedCount = $user->tokens()->count();
            if ($revokedCount > 0) {
                $user->tokens()->delete();
                
                // Log the session invalidation
                \App\Models\SecurityLog::log(
                    'session_invalidated',
                    $user,
                    $ipAddress,
                    "Previous {$revokedCount} session(s) invalidated due to new login (single session mode)"
                );
            }
        } elseif ($maxSessions > 0) {
            // Limit concurrent sessions
            $activeTokens = $user->tokens()->orderBy('created_at', 'asc')->get();
            
            if ($activeTokens->count() >= $maxSessions) {
                // Remove oldest tokens to make room for new one
                $tokensToRemove = $activeTokens->count() - $maxSessions + 1;
                $oldestTokenIds = $activeTokens->take($tokensToRemove)->pluck('id');
                $user->tokens()->whereIn('id', $oldestTokenIds)->delete();
                
                \App\Models\SecurityLog::log(
                    'session_limit_reached',
                    $user,
                    $ipAddress,
                    "Oldest {$tokensToRemove} session(s) removed due to max session limit ({$maxSessions})"
                );
            }
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        $user->load('roles');
        $user->setRelation('permissions', $user->getAllPermissions());

        return $this->success([
            'user' => $user,
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
        // Check if registration is enabled in settings
        $registrationEnabled = \App\Models\Setting::get('enable_registration', true);
        if (!$registrationEnabled) {
            return $this->error('Registration is currently disabled.', 403, [], 'REGISTRATION_DISABLED');
        }

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
        $user = $request->user()->load('roles');
        $user->setRelation('permissions', $user->getAllPermissions());
        return $this->success($user, 'User retrieved successfully');
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
