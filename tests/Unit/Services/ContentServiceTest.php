<?php

namespace Tests\Unit\Services;

use App\Models\Category;
use App\Models\Content;
use App\Models\ContentRevision;
use App\Models\Tag;
use App\Models\User;
use App\Services\ContentService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ContentServiceTest extends TestCase
{
    protected ContentService $service;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->service = new ContentService;
        $this->user = User::factory()->create();
    }

    /**
     * Test create content.
     */
    public function test_create_content(): void
    {
        $category = Category::factory()->create();

        $data = [
            'title' => 'Test Content '.uniqid(),
            'slug' => 'test-content-'.uniqid(),
            'body' => 'This is test content body.',
            'status' => 'draft',
            'type' => 'post',
            'category_id' => $category->id,
        ];

        $content = $this->service->create($data, $this->user->id);

        $this->assertInstanceOf(Content::class, $content);
        $this->assertEquals($data['title'], $content->title);
        $this->assertEquals($data['slug'], $content->slug);
        $this->assertEquals($this->user->id, $content->author_id);
    }

    /**
     * Test create content with revision.
     */
    public function test_create_content_with_revision(): void
    {
        $data = [
            'title' => 'Revision Test '.uniqid(),
            'slug' => 'revision-test-'.uniqid(),
            'body' => 'Content with revision.',
            'status' => 'published',
            'type' => 'post',
        ];

        $content = $this->service->create($data, $this->user->id, true);

        $this->assertInstanceOf(Content::class, $content);

        // Check revision was created
        $revision = ContentRevision::where('content_id', $content->id)->first();
        $this->assertNotNull($revision);
    }

    /**
     * Test update content.
     */
    public function test_update_content(): void
    {
        $content = Content::factory()->create();

        $updateData = [
            'title' => 'Updated Title '.uniqid(),
            'body' => 'Updated body content',
        ];

        $updated = $this->service->update($content, $updateData, $this->user->id);

        $this->assertEquals($updateData['title'], $updated->title);
        $this->assertEquals($updateData['body'], $updated->body);
    }

    /**
     * Test update content with tags.
     */
    public function test_update_content_with_tags(): void
    {
        $content = Content::factory()->create();
        $tags = Tag::factory()->count(3)->create();

        $updateData = [
            'title' => 'Tagged Content '.uniqid(),
            'tags' => $tags->pluck('id')->toArray(),
        ];

        $updated = $this->service->update($content, $updateData, $this->user->id);

        $this->assertEquals(3, $updated->tags()->count());
    }

    /**
     * Test update creates revision when requested.
     */
    public function test_update_creates_revision(): void
    {
        $content = Content::factory()->create();

        $updateData = [
            'title' => 'Revised Content '.uniqid(),
            'body' => 'New body',
        ];

        $this->service->update($content, $updateData, $this->user->id, true, 'Test revision note');

        $revision = ContentRevision::where('content_id', $content->id)
            ->where('reason', 'Test revision note')
            ->first();

        $this->assertNotNull($revision);
    }

    /**
     * Test toggle featured status.
     */
    public function test_toggle_featured(): void
    {
        $content = Content::factory()->create(['is_featured' => false]);

        $result = $this->service->toggleFeatured($content);

        // Returns the new is_featured value (may be 1/0 from DB)
        $this->assertEquals(true, (bool) $result);
        $content->refresh();
        $this->assertEquals(true, (bool) $content->is_featured);

        // Toggle again
        $result = $this->service->toggleFeatured($content);

        $this->assertEquals(false, (bool) $result);
    }

    /**
     * Test delete content (soft delete).
     */
    public function test_delete_content(): void
    {
        $content = Content::factory()->create();
        $contentId = $content->id;

        $this->service->delete($content); // Returns void

        $this->assertSoftDeleted('contents', ['id' => $contentId]);
    }

    /**
     * Test duplicate content.
     */
    public function test_duplicate_content(): void
    {
        $content = Content::factory()->create([
            'title' => 'Original Content',
        ]);
        $tags = Tag::factory()->count(2)->create();
        $content->tags()->attach($tags);

        $duplicate = $this->service->duplicate($content, $this->user->id);

        $this->assertNotEquals($content->id, $duplicate->id);
        $this->assertEquals('Original Content (Copy)', $duplicate->title);
        $this->assertEquals('draft', $duplicate->status);
        $this->assertEquals(2, $duplicate->tags()->count());
    }

    /**
     * Test bulk publish action.
     */
    public function test_bulk_publish_action(): void
    {
        $contents = Content::factory()->count(3)->create(['status' => 'draft']);
        $ids = $contents->pluck('id')->toArray();

        $result = $this->service->bulkAction('publish', $ids);

        foreach ($contents as $content) {
            $content->refresh();
            $this->assertEquals('published', $content->status);
        }

        $this->assertEquals(3, $result); // Returns int count, not array
    }

    /**
     * Test bulk delete action.
     */
    public function test_bulk_delete_action(): void
    {
        $contents = Content::factory()->count(2)->create();
        $ids = $contents->pluck('id')->toArray();

        $result = $this->service->bulkAction('delete', $ids);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('contents', ['id' => $id]);
        }

        $this->assertEquals(2, $result); // Returns int count
    }

    /**
     * Test lock content for editing.
     */
    public function test_lock_content(): void
    {
        $content = Content::factory()->create();

        $this->service->lock($content, $this->user->id); // Returns void

        $content->refresh();
        $this->assertEquals($this->user->id, $content->locked_by);
        $this->assertNotNull($content->locked_at);
    }

    /**
     * Test unlock content.
     */
    public function test_unlock_content(): void
    {
        $content = Content::factory()->create([
            'locked_by' => $this->user->id,
            'locked_at' => now(),
        ]);

        $this->service->unlock($content); // Returns void

        $content->refresh();
        $this->assertNull($content->locked_by);
        $this->assertNull($content->locked_at);
    }

    /**
     * Test is locked by other user.
     */
    public function test_is_locked_by_other(): void
    {
        $otherUser = User::factory()->create();
        $content = Content::factory()->create([
            'locked_by' => $otherUser->id,
            'locked_at' => now(),
        ]);

        $this->assertTrue($this->service->isLockedByOther($content, $this->user->id));
        $this->assertFalse($this->service->isLockedByOther($content, $otherUser->id));
    }

    /**
     * Test generate unique slug.
     */
    public function test_generate_unique_slug(): void
    {
        // Create content with known slug
        Content::factory()->create(['slug' => 'test-slug']);

        $slug = $this->service->generateUniqueSlug('Test Slug');

        // Should have suffix since 'test-slug' already exists
        $this->assertStringStartsWith('test-slug', $slug);
        $this->assertNotEquals('test-slug', $slug);
    }

    /**
     * Test generate unique slug with exclusion.
     */
    public function test_generate_unique_slug_with_exclusion(): void
    {
        $content = Content::factory()->create(['slug' => 'my-content']);

        // When updating the same content, should allow the same slug
        $slug = $this->service->generateUniqueSlug('My Content', $content->id);

        $this->assertEquals('my-content', $slug);
    }

    /**
     * Test restore trashed content.
     */
    public function test_restore_trashed_content(): void
    {
        $content = Content::factory()->create();
        $content->delete();

        $this->assertSoftDeleted('contents', ['id' => $content->id]);

        $result = $this->service->restore($content->id); // Returns bool

        $this->assertTrue($result);

        $content->refresh();
        $this->assertNull($content->deleted_at);
    }

    /**
     * Test force delete content.
     */
    public function test_force_delete_content(): void
    {
        $content = Content::factory()->create();
        $content->delete();
        $contentId = $content->id;

        $result = $this->service->forceDelete($contentId);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('contents', ['id' => $contentId]);
    }
}
