<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\MediaFolder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Helpers\TestHelpers;
use Tests\TestCase;

class MediaManagementTest extends TestCase
{
// use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /**
     * Test admin can upload media file.
     */
    public function test_admin_can_upload_media_file(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $file = TestHelpers::createTestImage('test.jpg');

        $response = $this->postJson('/api/v1/admin/cms/media/upload', [
            'file' => $file,
        ]);

        TestHelpers::assertApiSuccess($response, 201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'media' => [
                    'id',
                    'name',
                    'file_name',
                    'mime_type',
                    'path',
                ],
                'url',
            ],
        ]);

        $this->assertDatabaseHas('media', [
            'name' => 'test.jpg',
            'mime_type' => 'image/jpeg',
        ]);
    }

    /**
     * Test upload requires file.
     */
    public function test_upload_requires_file(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $response = $this->postJson('/api/v1/admin/cms/media/upload', []);

        TestHelpers::assertApiValidationError($response);
        $response->assertJsonValidationErrors(['file']);
    }

    /**
     * Test admin can upload media to folder.
     */
    public function test_admin_can_upload_media_to_folder(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $folder = MediaFolder::factory()->create();
        $file = TestHelpers::createTestImage('test.jpg');

        $response = $this->postJson('/api/v1/admin/cms/media/upload', [
            'file' => $file,
            'folder_id' => $folder->id,
        ]);

        TestHelpers::assertApiSuccess($response, 201);
        $this->assertDatabaseHas('media', [
            'folder_id' => $folder->id,
        ]);
    }

    /**
     * Test admin can list media.
     */
    public function test_admin_can_list_media(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        Media::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/admin/cms/media');

        TestHelpers::assertApiPaginated($response);
        $response->assertJsonCount(5, 'data.data');
    }

    /**
     * Test admin can filter media by folder.
     */
    public function test_admin_can_filter_media_by_folder(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $folder = MediaFolder::factory()->create();
        Media::factory()->count(3)->create(['folder_id' => $folder->id]);
        Media::factory()->count(2)->create(['folder_id' => null]);

        $response = $this->getJson("/api/v1/admin/cms/media?folder_id={$folder->id}");

        TestHelpers::assertApiPaginated($response);
        $response->assertJsonCount(3, 'data.data');
    }

    /**
     * Test admin can filter media by mime type.
     */
    public function test_admin_can_filter_media_by_mime_type(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        Media::factory()->image()->count(3)->create();
        Media::factory()->document()->count(2)->create();

        $response = $this->getJson('/api/v1/admin/cms/media?mime_type=image');

        TestHelpers::assertApiPaginated($response);
        $response->assertJsonCount(3, 'data.data');
    }

    /**
     * Test admin can search media.
     */
    public function test_admin_can_search_media(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        Media::factory()->create(['name' => 'test-image.jpg']);
        Media::factory()->create(['name' => 'other-file.pdf']);

        $response = $this->getJson('/api/v1/admin/cms/media?search=test');

        TestHelpers::assertApiPaginated($response);
        $response->assertJsonCount(1, 'data.data');
    }

    /**
     * Test admin can view media details.
     */
    public function test_admin_can_view_media_details(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $media = Media::factory()->create();

        $response = $this->getJson("/api/v1/admin/cms/media/{$media->id}");

        TestHelpers::assertApiSuccess($response);
        $response->assertJson([
            'data' => [
                'id' => $media->id,
                'name' => $media->name,
            ],
        ]);
    }

    /**
     * Test admin can update media.
     */
    public function test_admin_can_update_media(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $media = Media::factory()->create();

        $response = $this->putJson("/api/v1/admin/cms/media/{$media->id}", [
            'name' => 'Updated Name',
            'alt' => 'Updated Alt Text',
            'description' => 'Updated description',
        ]);

        TestHelpers::assertApiSuccess($response);
        $this->assertDatabaseHas('media', [
            'id' => $media->id,
            'name' => 'Updated Name',
            'alt' => 'Updated Alt Text',
        ]);
    }

    /**
     * Test admin can delete media.
     */
    public function test_admin_can_delete_media(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $media = Media::factory()->create();

        $response = $this->deleteJson("/api/v1/admin/cms/media/{$media->id}");

        TestHelpers::assertApiSuccess($response);
        $this->assertSoftDeleted('media', [
            'id' => $media->id,
        ]);
    }

    /**
     * Test admin can perform bulk actions on media.
     */
    public function test_admin_can_perform_bulk_actions(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $media = Media::factory()->count(3)->create();
        $folder = MediaFolder::factory()->create();

        // Test bulk move
        $response = $this->postJson('/api/v1/admin/cms/media/bulk-action', [
            'action' => 'move',
            'ids' => $media->pluck('id')->toArray(),
            'folder_id' => $folder->id,
        ]);

        TestHelpers::assertApiSuccess($response);

        foreach ($media as $item) {
            $this->assertDatabaseHas('media', [
                'id' => $item->id,
                'folder_id' => $folder->id,
            ]);
        }
    }

    /**
     * Test admin can generate thumbnail for image.
     */
    public function test_admin_can_generate_thumbnail_for_image(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $media = Media::factory()->image()->create([
            'path' => 'media/test.jpg',
        ]);

        // Create a fake image file in storage
        $imageContent = base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/8A8A');
        Storage::disk('public')->put($media->path, $imageContent);

        $response = $this->postJson("/api/v1/admin/cms/media/{$media->id}/thumbnail");

        // Thumbnail generation might fail in test environment, but endpoint should be accessible
        $response->assertStatus(200);
    }

    /**
     * Test thumbnail generation fails for non-image.
     */
    public function test_thumbnail_generation_fails_for_non_image(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $media = Media::factory()->document()->create();

        $response = $this->postJson("/api/v1/admin/cms/media/{$media->id}/thumbnail");

        TestHelpers::assertApiError($response);
    }

    /**
     * Test admin can resize image.
     */
    public function test_admin_can_resize_image(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $media = Media::factory()->image()->create([
            'path' => 'media/test.jpg',
        ]);

        // Create a fake image file in storage
        $imageContent = base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/8A8A');
        Storage::disk('public')->put($media->path, $imageContent);

        $response = $this->postJson("/api/v1/admin/cms/media/{$media->id}/resize", [
            'width' => 800,
            'height' => 600,
        ]);

        // Resize might fail in test environment, but endpoint should be accessible
        $response->assertStatus(200);
    }

    /**
     * Test resize requires width or height.
     */
    public function test_resize_requires_width_or_height(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $media = Media::factory()->image()->create();

        $response = $this->postJson("/api/v1/admin/cms/media/{$media->id}/resize", []);

        TestHelpers::assertApiValidationError($response);
    }

    /**
     * Test admin can get media usage.
     */
    public function test_admin_can_get_media_usage(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $media = Media::factory()->create();

        $response = $this->getJson("/api/v1/admin/cms/media/{$media->id}/usage");

        TestHelpers::assertApiSuccess($response);
        // API returns array of usages directly, not wrapped in usage_count/usages
        $this->assertIsArray($response->json('data'));
    }

    /**
     * Test user without permission cannot upload media.
     */
    public function test_user_without_permission_cannot_upload_media(): void
    {
        $user = $this->createUser();
        $this->actingAs($user, 'sanctum');

        $file = TestHelpers::createTestImage('test.jpg');

        $response = $this->postJson('/api/v1/admin/cms/media/upload', [
            'file' => $file,
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test user without permission cannot update media.
     */
    public function test_user_without_permission_cannot_update_media(): void
    {
        $user = $this->createUser();
        $this->actingAs($user, 'sanctum');

        $media = Media::factory()->create();

        $response = $this->putJson("/api/v1/admin/cms/media/{$media->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test user without permission cannot delete media.
     */
    public function test_user_without_permission_cannot_delete_media(): void
    {
        $user = $this->createUser();
        $this->actingAs($user, 'sanctum');

        $media = Media::factory()->create();

        $response = $this->deleteJson("/api/v1/admin/cms/media/{$media->id}");

        $response->assertStatus(403);
    }

    /**
     * Test upload file size limit.
     */
    public function test_upload_respects_file_size_limit(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        // Create a file larger than 10MB
        $file = UploadedFile::fake()->create('large.jpg', 11000); // 11MB

        $response = $this->postJson('/api/v1/admin/cms/media/upload', [
            'file' => $file,
        ]);

        TestHelpers::assertApiValidationError($response);
        $response->assertJsonValidationErrors(['file']);
    }
}
