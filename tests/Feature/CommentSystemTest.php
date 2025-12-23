<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\TestHelpers;
use Tests\TestCase;

class CommentSystemTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user can post a comment on published content.
     */
    public function test_user_can_post_comment_on_published_content(): void
    {
        $user = $this->createUser();
        $content = Content::factory()->published()->create();

        $response = $this->postJson("/api/v1/cms/contents/{$content->id}/comments", [
            'body' => 'This is a test comment',
            'author_name' => $user->name,
            'author_email' => $user->email,
        ]);

        TestHelpers::assertApiSuccess($response, 201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'body',
                'author_name',
                'status',
            ],
        ]);

        $this->assertDatabaseHas('comments', [
            'content_id' => $content->id,
            'body' => 'This is a test comment',
        ]);
    }

    /**
     * Test comment requires body.
     */
    public function test_comment_requires_body(): void
    {
        $content = Content::factory()->published()->create();

        $response = $this->postJson("/api/v1/cms/contents/{$content->id}/comments", [
            'author_name' => 'Test User',
            'author_email' => 'test@example.com',
        ]);

        TestHelpers::assertApiValidationError($response);
        $response->assertJsonValidationErrors(['body']);
    }

    /**
     * Test user can view approved comments.
     */
    public function test_user_can_view_approved_comments(): void
    {
        $content = Content::factory()->published()->create();
        
        // Create approved comments
        $content->comments()->createMany([
            [
                'body' => 'First comment',
                'author_name' => 'User 1',
                'author_email' => 'user1@example.com',
                'status' => 'approved',
            ],
            [
                'body' => 'Second comment',
                'author_name' => 'User 2',
                'author_email' => 'user2@example.com',
                'status' => 'approved',
            ],
        ]);

        $response = $this->getJson("/api/v1/cms/contents/{$content->id}/comments");

        TestHelpers::assertApiSuccess($response);
        $response->assertJsonCount(2, 'data');
    }

    /**
     * Test pending comments are not visible to public.
     */
    public function test_pending_comments_not_visible_to_public(): void
    {
        $content = Content::factory()->published()->create();
        
        $content->comments()->create([
            'body' => 'Pending comment',
            'author_name' => 'User',
            'author_email' => 'user@example.com',
            'status' => 'pending',
        ]);

        $response = $this->getJson("/api/v1/cms/contents/{$content->id}/comments");

        TestHelpers::assertApiSuccess($response);
        $response->assertJsonCount(0, 'data');
    }

    /**
     * Test admin can approve comment.
     */
    public function test_admin_can_approve_comment(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $content = Content::factory()->published()->create();
        $comment = $content->comments()->create([
            'body' => 'Pending comment',
            'author_name' => 'User',
            'author_email' => 'user@example.com',
            'status' => 'pending',
        ]);

        $response = $this->putJson("/api/v1/admin/cms/comments/{$comment->id}/approve");

        TestHelpers::assertApiSuccess($response);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'status' => 'approved',
        ]);
    }

    /**
     * Test admin can reject comment.
     */
    public function test_admin_can_reject_comment(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $content = Content::factory()->published()->create();
        $comment = $content->comments()->create([
            'body' => 'Pending comment',
            'author_name' => 'User',
            'author_email' => 'user@example.com',
            'status' => 'pending',
        ]);

        $response = $this->putJson("/api/v1/admin/cms/comments/{$comment->id}/reject");

        TestHelpers::assertApiSuccess($response);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'status' => 'rejected',
        ]);
    }

    /**
     * Test admin can delete comment.
     */
    public function test_admin_can_delete_comment(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $content = Content::factory()->published()->create();
        $comment = $content->comments()->create([
            'body' => 'Test comment',
            'author_name' => 'User',
            'author_email' => 'user@example.com',
            'status' => 'approved',
        ]);

        $response = $this->deleteJson("/api/v1/admin/cms/comments/{$comment->id}");

        TestHelpers::assertApiSuccess($response);
        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id,
        ]);
    }

    /**
     * Test comment is rate limited.
     */
    public function test_comment_submission_is_rate_limited(): void
    {
        $content = Content::factory()->published()->create();

        // Make 11 requests (limit is 10 per minute)
        for ($i = 0; $i < 11; $i++) {
            $response = $this->postJson("/api/v1/cms/contents/{$content->id}/comments", [
                'body' => "Comment {$i}",
                'author_name' => 'Test User',
                'author_email' => 'test@example.com',
            ]);
        }

        // 11th request should be rate limited
        $response->assertStatus(429);
    }
}

