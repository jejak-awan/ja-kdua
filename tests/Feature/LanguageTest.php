<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\Translation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\TestHelpers;
use Tests\TestCase;

class LanguageTest extends TestCase
{
    // use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createAdminUser();
    }

    /**
     * Test admin can list all languages.
     */
    public function test_admin_can_list_languages(): void
    {
        Language::factory()->count(3)->create(['is_active' => true]);
        Language::factory()->count(2)->create(['is_active' => false]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/ja/languages');

        TestHelpers::assertApiSuccess($response);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'code',
                    'name',
                    'is_active',
                    'is_default',
                ],
            ],
        ]);

        // Should only return active languages
        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Test admin can create a language.
     */
    public function test_admin_can_create_language(): void
    {
        $languageData = [
            'code' => 'id',
            'name' => 'Indonesian',
            'native_name' => 'Bahasa Indonesia',
            'flag' => '🇮🇩',
            'is_active' => true,
            'is_default' => false,
            'sort_order' => 1,
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/ja/languages', $languageData);

        TestHelpers::assertApiSuccess($response, 201);
        $response->assertJsonFragment([
            'code' => 'id',
            'name' => 'Indonesian',
            'native_name' => 'Bahasa Indonesia',
        ]);

        $this->assertDatabaseHas('languages', [
            'code' => 'id',
            'name' => 'Indonesian',
        ]);
    }

    /**
     * Test language creation requires code and name.
     */
    public function test_language_creation_requires_code_and_name(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/ja/languages', []);

        TestHelpers::assertApiValidationError($response);
        $response->assertJsonValidationErrors(['code', 'name']);
    }

    /**
     * Test language code must be unique.
     */
    public function test_language_code_must_be_unique(): void
    {
        Language::factory()->create(['code' => 'en']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/ja/languages', [
                'code' => 'en',
                'name' => 'English',
            ]);

        TestHelpers::assertApiValidationError($response);
        $response->assertJsonValidationErrors(['code']);
    }

    /**
     * Test setting a language as default unsets other defaults.
     */
    public function test_setting_language_as_default_unsets_other_defaults(): void
    {
        $existingDefault = Language::factory()->create([
            'is_default' => true,
            'code' => 'en',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/ja/languages', [
                'code' => 'id',
                'name' => 'Indonesian',
                'is_default' => true,
            ]);

        TestHelpers::assertApiSuccess($response, 201);

        $this->assertDatabaseHas('languages', [
            'code' => 'id',
            'is_default' => true,
        ]);

        $this->assertDatabaseHas('languages', [
            'id' => $existingDefault->id,
            'is_default' => false,
        ]);
    }

    /**
     * Test admin can update a language.
     */
    public function test_admin_can_update_language(): void
    {
        $language = Language::factory()->create([
            'code' => 'en',
            'name' => 'English',
        ]);

        $updateData = [
            'name' => 'English (US)',
            'native_name' => 'English (US)',
            'is_active' => false,
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/admin/ja/languages/{$language->id}", $updateData);

        TestHelpers::assertApiSuccess($response);
        $response->assertJsonFragment([
            'name' => 'English (US)',
            'native_name' => 'English (US)',
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('languages', [
            'id' => $language->id,
            'name' => 'English (US)',
        ]);
    }

    /**
     * Test admin can delete a language.
     */
    public function test_admin_can_delete_language(): void
    {
        $language = Language::factory()->create([
            'is_default' => false,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/admin/ja/languages/{$language->id}");

        TestHelpers::assertApiSuccess($response);

        $this->assertDatabaseMissing('languages', [
            'id' => $language->id,
        ]);
    }

    /**
     * Test cannot delete default language.
     */
    public function test_cannot_delete_default_language(): void
    {
        $language = Language::factory()->create([
            'is_default' => true,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/admin/ja/languages/{$language->id}");

        TestHelpers::assertApiValidationError($response);
        $response->assertJsonValidationErrors(['language']);

        $this->assertDatabaseHas('languages', [
            'id' => $language->id,
        ]);
    }

    /**
     * Test admin can get translations for an entity.
     */
    public function test_admin_can_get_translations_for_entity(): void
    {
        $this->markTestSkipped('Translation model and routes not yet implemented.');
    }

    /**
     * Test admin can set translation for an entity.
     */
    public function test_admin_can_set_translation_for_entity(): void
    {
        $this->markTestSkipped('Translation model and routes not yet implemented.');
    }

    /**
     * Test translation requires all fields.
     */
    public function test_translation_requires_all_fields(): void
    {
        $this->markTestSkipped('Translation model and routes not yet implemented.');
    }

    /**
     * Test unauthenticated user cannot access languages.
     */
    public function test_unauthenticated_user_cannot_access_languages(): void
    {
        $response = $this->getJson('/api/v1/admin/ja/languages');
        $response->assertStatus(401);

        $response = $this->postJson('/api/v1/admin/ja/languages', []);
        $response->assertStatus(401);
    }

    /**
     * Test user without permission cannot manage languages.
     */
    public function test_user_without_permission_cannot_manage_languages(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/admin/ja/languages');

        $response->assertStatus(403);
    }
}
