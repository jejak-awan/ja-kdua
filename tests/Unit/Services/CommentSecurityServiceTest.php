<?php

namespace Tests\Unit\Services;

use App\Models\Setting;
use App\Services\CommentSecurityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentSecurityServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CommentSecurityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CommentSecurityService();
    }

    public function test_detects_banned_words()
    {
        Setting::set('comments.security.banned_words', json_encode(['casino', 'spam']));

        $this->assertTrue($this->service->isSpam('Visit my casino now!'));
        $this->assertFalse($this->service->isSpam('This is a nice post.'));
    }

    public function test_detects_excessive_links()
    {
        Setting::set('comments.security.max_links', 1);

        $this->assertTrue($this->service->isSpam('http://link1.com and https://link2.com'));
        $this->assertFalse($this->service->isSpam('Check http://link1.com'));
    }

    public function test_determines_initial_status()
    {
        Setting::set('comments.security.moderation_enabled', true);
        $this->assertEquals('spam', $this->service->getInitialStatus(true));
        $this->assertEquals('pending', $this->service->getInitialStatus(false));

        Setting::set('comments.security.moderation_enabled', false);
        $this->assertEquals('approved', $this->service->getInitialStatus(false));
    }
}
