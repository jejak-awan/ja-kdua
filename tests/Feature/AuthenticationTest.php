<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Helpers\TestHelpers;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful login with valid credentials.
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        TestHelpers::assertApiSuccess($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user',
                'token',
            ],
        ]);
        $response->assertJson([
            'data' => [
                'user' => [
                    'email' => 'test@example.com',
                ],
            ],
        ]);
    }

    /**
     * Test login fails with invalid credentials.
     */
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * Test login fails with unverified email.
     */
    public function test_user_cannot_login_with_unverified_email(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Please verify your email address before logging in.',
        ]);
    }

    /**
     * Test login validation requires email and password.
     */
    public function test_login_requires_email_and_password(): void
    {
        $response = $this->postJson('/api/v1/login', []);

        TestHelpers::assertApiValidationError($response);
        $response->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * Test successful user registration.
     */
    public function test_user_can_register_with_valid_data(): void
    {
        $userData = TestHelpers::getUserData();

        $response = $this->postJson('/api/v1/register', $userData);

        TestHelpers::assertApiSuccess($response, 201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user',
                'token',
            ],
        ]);
        
        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
        ]);
    }

    /**
     * Test registration validation requires all fields.
     */
    public function test_registration_requires_all_fields(): void
    {
        $response = $this->postJson('/api/v1/register', []);

        TestHelpers::assertApiValidationError($response);
        $response->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /**
     * Test registration fails with duplicate email.
     */
    public function test_registration_fails_with_duplicate_email(): void
    {
        $user = User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $response = $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        TestHelpers::assertApiValidationError($response);
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * Test registration requires password confirmation.
     */
    public function test_registration_requires_password_confirmation(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'different-password',
        ]);

        TestHelpers::assertApiValidationError($response);
        $response->assertJsonValidationErrors(['password']);
    }

    /**
     * Test authenticated user can logout.
     */
    public function test_authenticated_user_can_logout(): void
    {
        $user = $this->createUser();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->postJson('/api/v1/logout', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        TestHelpers::assertApiSuccess($response);
    }

    /**
     * Test unauthenticated user cannot logout.
     */
    public function test_unauthenticated_user_cannot_logout(): void
    {
        $response = $this->postJson('/api/v1/logout');

        $response->assertStatus(401);
    }

    /**
     * Test authenticated user can get their profile.
     */
    public function test_authenticated_user_can_get_profile(): void
    {
        $user = $this->createUser();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/user');

        TestHelpers::assertApiSuccess($response);
        $response->assertJson([
            'data' => [
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Test unauthenticated user cannot get profile.
     */
    public function test_unauthenticated_user_cannot_get_profile(): void
    {
        $response = $this->getJson('/api/v1/user');

        $response->assertStatus(401);
    }

    /**
     * Test user can request password reset.
     */
    public function test_user_can_request_password_reset(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        Notification::fake();

        $response = $this->postJson('/api/v1/forgot-password', [
            'email' => 'test@example.com',
        ]);

        TestHelpers::assertApiSuccess($response);
    }

    /**
     * Test password reset requires valid email.
     */
    public function test_password_reset_requires_valid_email(): void
    {
        $response = $this->postJson('/api/v1/forgot-password', [
            'email' => 'invalid-email',
        ]);

        TestHelpers::assertApiValidationError($response);
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * Test user can reset password with valid token.
     */
    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('old-password'),
        ]);

        // Request password reset first to get token
        Notification::fake();
        $this->postJson('/api/v1/forgot-password', [
            'email' => 'test@example.com',
        ]);

        // Get the token from the database
        $passwordReset = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('email', 'test@example.com')
            ->first();

        // We need to get the plain token, but it's hashed in DB
        // For testing, we'll create a token manually
        $token = \Illuminate\Support\Str::random(64);
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => 'test@example.com'],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        $response = $this->postJson('/api/v1/reset-password', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        TestHelpers::assertApiSuccess($response);
        
        // Verify password was changed
        $user->refresh();
        $this->assertTrue(Hash::check('new-password', $user->password));
    }

    /**
     * Test password reset fails with invalid token.
     */
    public function test_password_reset_fails_with_invalid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->postJson('/api/v1/reset-password', [
            'token' => 'invalid-token',
            'email' => 'test@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        TestHelpers::assertApiError($response);
    }

    /**
     * Test password reset requires all fields.
     */
    public function test_password_reset_requires_all_fields(): void
    {
        $response = $this->postJson('/api/v1/reset-password', []);

        TestHelpers::assertApiValidationError($response);
        $response->assertJsonValidationErrors(['token', 'email', 'password']);
    }

    /**
     * Test login is rate limited.
     */
    public function test_login_is_rate_limited(): void
    {
        // Make 6 requests (limit is 5 per minute)
        for ($i = 0; $i < 6; $i++) {
            $response = $this->postJson('/api/v1/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
        }

        // 6th request should be rate limited
        $response->assertStatus(429);
    }
}

