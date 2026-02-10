<?php

namespace Tests\Unit\Services;

use App\Models\Core\IpList;
use App\Models\Core\SecurityLog;
use App\Models\Core\User;
use App\Services\Core\SecurityService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SecurityServiceTest extends TestCase
{
    protected SecurityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->service = new SecurityService;
    }

    /**
     * Test localhost is protected and never blocked.
     */
    public function test_localhost_is_protected(): void
    {
        $this->assertTrue($this->service->isProtectedIp('127.0.0.1'));
        $this->assertTrue($this->service->isProtectedIp('::1'));
        $this->assertTrue($this->service->isProtectedIp('localhost'));
    }

    /**
     * Test regular IP is not protected.
     */
    public function test_regular_ip_not_protected(): void
    {
        $this->assertFalse($this->service->isProtectedIp('192.168.1.1'));
        $this->assertFalse($this->service->isProtectedIp('8.8.8.8'));
    }

    /**
     * Test failed login increments counter.
     */
    public function test_record_failed_login_increments_counter(): void
    {
        $email = 'test@example.com';
        $ip = '192.168.1.100';

        $result = $this->service->recordFailedLogin($email, $ip);

        $this->assertEquals(1, $result['ip_attempts']);
        $this->assertEquals(1, $result['email_attempts']);
        $this->assertFalse($result['ip_blocked']);
        $this->assertFalse($result['account_locked']);
    }

    /**
     * Test multiple failed logins trigger blocking.
     */
    public function test_multiple_failed_logins_trigger_blocking(): void
    {
        $email = 'test@example.com';
        $ip = '10.0.0.99'; // Non-localhost IP

        // Simulate max failed attempts
        $maxAttempts = $this->service->getMaxFailedAttempts();

        for ($i = 1; $i <= $maxAttempts; $i++) {
            $result = $this->service->recordFailedLogin($email, $ip);
        }

        // After max attempts, should be blocked
        $this->assertTrue($result['ip_blocked'] || $result['account_locked']);
    }

    /**
     * Test successful login clears security cache.
     */
    public function test_successful_login_clears_cache(): void
    {
        $user = User::factory()->create();
        $ip = '192.168.1.100';

        // Record some failed attempts first
        $this->service->recordFailedLogin($user->email, $ip);
        $this->service->recordFailedLogin($user->email, $ip);

        $this->assertGreaterThan(0, $this->service->getFailedAttempts($ip));

        // Successful login should clear cache
        $this->service->recordSuccessfulLogin($user, $ip);

        $this->assertEquals(0, $this->service->getFailedAttempts($ip));
    }

    /**
     * Test account locking and unlocking.
     */
    public function test_account_lock_and_unlock(): void
    {
        $email = 'testlock@example.com';

        $this->assertFalse($this->service->isAccountLocked($email));

        $this->service->lockAccount($email, 'Test lock');

        $this->assertTrue($this->service->isAccountLocked($email));

        $this->service->unlockAccount($email);

        $this->assertFalse($this->service->isAccountLocked($email));
    }

    /**
     * Test get account lockout remaining time.
     */
    public function test_get_account_lockout_remaining(): void
    {
        $email = 'testtime@example.com';

        // No lock - should be 0
        $this->assertEquals(0, $this->service->getAccountLockoutRemaining($email));

        $this->service->lockAccount($email);

        // Should have some remaining time
        $remaining = $this->service->getAccountLockoutRemaining($email);
        $this->assertGreaterThan(0, $remaining);
    }

    /**
     * Test temporary IP blocking.
     */
    public function test_temporary_ip_blocking(): void
    {
        $ip = '10.0.0.50';

        $this->assertFalse($this->service->isIpBlocked($ip));

        $duration = $this->service->blockIpTemporarily($ip, 'Test block');

        $this->assertGreaterThan(0, $duration);
        $this->assertTrue($this->service->isIpBlocked($ip));
    }

    /**
     * Test protected IP cannot be blocked.
     */
    public function test_protected_ip_cannot_be_blocked(): void
    {
        $ip = '127.0.0.1';

        $duration = $this->service->blockIpTemporarily($ip, 'Test');

        $this->assertEquals(0, $duration);
        $this->assertFalse($this->service->isIpBlocked($ip));
    }

    /**
     * Test get block info.
     */
    public function test_get_block_info(): void
    {
        $ip = '10.0.0.60';

        $info = $this->service->getBlockInfo($ip);

        $this->assertArrayHasKey('is_blocked', $info);
        $this->assertArrayHasKey('remaining_seconds', $info);
        $this->assertArrayHasKey('offense_count', $info);
        $this->assertArrayHasKey('failed_attempts', $info);
    }

    /**
     * Test permanent IP blocking and unblocking.
     */
    public function test_permanent_block_and_unblock(): void
    {
        $ip = '10.0.0.70';

        $result = $this->service->blockIpPermanently($ip, 'Test permanent block');

        $this->assertTrue($result);
        $this->assertTrue($this->service->isIpBlocked($ip));

        $this->service->unblockIp($ip);

        $this->assertFalse($this->service->isIpBlocked($ip));
    }

    /**
     * Test whitelist management.
     */
    public function test_whitelist_management(): void
    {
        $ip = '172.16.0.1';

        $this->service->addToWhitelist($ip, 'Test whitelist');

        $this->assertTrue(IpList::isWhitelisted($ip));

        // Whitelisted IP cannot be blocked
        $duration = $this->service->blockIpTemporarily($ip);
        $this->assertEquals(0, $duration);

        $this->service->removeFromWhitelist($ip);

        $this->assertFalse(IpList::isWhitelisted($ip));
    }

    /**
     * Test get security statistics.
     */
    public function test_get_security_stats(): void
    {
        $stats = $this->service->getSecurityStats(30);

        $this->assertArrayHasKey('total_events', $stats);
        $this->assertArrayHasKey('failed_logins', $stats);
        $this->assertArrayHasKey('successful_logins', $stats);
        $this->assertArrayHasKey('blocked_ips', $stats);
        $this->assertArrayHasKey('suspicious_activities', $stats);
        $this->assertArrayHasKey('recent_events', $stats);
    }

    /**
     * Test record suspicious activity.
     */
    public function test_record_suspicious_activity(): void
    {
        $this->service->recordSuspiciousActivity('Test suspicious activity', null, ['test' => 'data']);

        $log = SecurityLog::where('event_type', 'suspicious_activity')
            ->where('description', 'Test suspicious activity')
            ->first();

        $this->assertNotNull($log);
    }

    /**
     * Test get remaining block time for non-blocked IP.
     */
    public function test_get_remaining_block_time_non_blocked(): void
    {
        $ip = '192.168.99.99';

        $remaining = $this->service->getRemainingBlockTime($ip);

        $this->assertEquals(0, $remaining);
    }

    /**
     * Test lockout duration getter.
     */
    public function test_get_lockout_duration(): void
    {
        $duration = $this->service->getLockoutDuration();

        $this->assertIsInt($duration);
        $this->assertGreaterThan(0, $duration);
    }

    /**
     * Test max failed attempts getter.
     */
    public function test_get_max_failed_attempts(): void
    {
        $max = $this->service->getMaxFailedAttempts();

        $this->assertIsInt($max);
        $this->assertGreaterThan(0, $max);
    }
}
