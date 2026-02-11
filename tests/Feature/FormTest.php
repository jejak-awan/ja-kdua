<?php

namespace Tests\Feature;

use App\Models\Core\Form;
use App\Models\Core\FormSubmission;
use App\Models\Core\User;
use Tests\Helpers\TestHelpers;
use Tests\TestCase;

class FormTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    /**
     * Test admin can list all forms.
     */
    public function test_admin_can_list_all_forms(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        Form::factory()->count(3)->create();

        $response = $this->getJson('/api/core/admin/ja/forms');

        TestHelpers::assertApiSuccess($response);
    }

    /**
     * Test admin can create form.
     */
    public function test_admin_can_create_form(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $formData = [
            'name' => 'Contact Form '.uniqid(),
            'slug' => 'contact-form-'.uniqid(),
            'description' => 'A contact form',
            'is_active' => true,
        ];

        $response = $this->postJson('/api/core/admin/ja/forms', $formData);

        TestHelpers::assertApiSuccess($response, 201);
        $this->assertDatabaseHas('forms', [
            'name' => $formData['name'],
            'slug' => $formData['slug'],
        ]);
    }

    /**
     * Test form creation requires name.
     */
    public function test_form_creation_requires_name(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $response = $this->postJson('/api/core/admin/ja/forms', []);

        TestHelpers::assertApiValidationError($response);
        $response->assertJsonValidationErrors(['name']);
    }

    /**
     * Test admin can view form details.
     */
    public function test_admin_can_view_form_details(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $form = Form::factory()->create();

        $response = $this->getJson("/api/core/admin/ja/forms/{$form->id}");

        TestHelpers::assertApiSuccess($response);
        $response->assertJson([
            'data' => [
                'id' => $form->id,
                'name' => $form->name,
            ],
        ]);
    }

    /**
     * Test admin can update form.
     */
    public function test_admin_can_update_form(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $form = Form::factory()->create();

        $response = $this->putJson("/api/core/admin/ja/forms/{$form->id}", [
            'name' => 'Updated Form Name',
        ]);

        TestHelpers::assertApiSuccess($response);
        $this->assertDatabaseHas('forms', [
            'id' => $form->id,
            'name' => 'Updated Form Name',
        ]);
    }

    /**
     * Test admin can delete form.
     */
    public function test_admin_can_delete_form(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $form = Form::factory()->create();

        $response = $this->deleteJson("/api/core/admin/ja/forms/{$form->id}");

        TestHelpers::assertApiSuccess($response);
        $this->assertSoftDeleted('forms', [
            'id' => $form->id,
        ]);
    }

    /**
     * Test admin can add field to form.
     */

    /**
     * Test admin can duplicate form.
     */
    public function test_admin_can_duplicate_form(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $form = Form::factory()->create();

        $response = $this->postJson("/api/core/admin/ja/forms/{$form->id}/duplicate", [
            'title' => $form->name.' (Copy)',
            'slug' => $form->slug.'-copy',
        ]);

        TestHelpers::assertApiSuccess($response, 201);
    }

    /**
     * Test admin can view form submissions.
     */
    public function test_admin_can_view_form_submissions(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $form = Form::factory()->create();
        FormSubmission::factory()->count(3)->create(['form_id' => $form->id]);

        $response = $this->getJson("/api/core/admin/ja/forms/{$form->id}/submissions");

        TestHelpers::assertApiSuccess($response);
    }

    /**
     * Test public can view active form.
     */
    public function test_public_can_view_active_form(): void
    {
        $form = Form::factory()->create(['is_active' => true]);

        $response = $this->getJson("/api/core/cms/forms/{$form->slug}");

        $response->assertStatus(200);
        $response->assertJson([
            'name' => $form->name,
        ]);
    }

    /**
     * Test public can submit form.
     */
    public function test_public_can_submit_form(): void
    {
        $form = Form::factory()->create([
            'is_active' => true,
            'blocks' => [
                [
                    'type' => 'form_input',
                    'settings' => [
                        'field_id' => 'name',
                        'type' => 'text',
                        'is_required' => true,
                    ],
                ],
                [
                    'type' => 'form_input',
                    'settings' => [
                        'field_id' => 'email',
                        'type' => 'email',
                        'is_required' => true,
                    ],
                ],
            ],
        ]);

        $response = $this->postJson("/api/core/cms/forms/{$form->slug}/submit", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        TestHelpers::assertApiSuccess($response, 201);
    }

    /**
     * Test form submission validates required fields.
     */
    public function test_form_submission_validates_required_fields(): void
    {
        $form = Form::factory()->create([
            'is_active' => true,
            'blocks' => [
                [
                    'type' => 'form_input',
                    'settings' => [
                        'field_id' => 'email',
                        'type' => 'email',
                        'is_required' => true,
                    ],
                ],
            ],
        ]);

        $response = $this->postJson("/api/core/cms/forms/{$form->slug}/submit", []);

        TestHelpers::assertApiValidationError($response);
    }

    /**
     * Test unauthenticated user cannot manage forms.
     */
    public function test_unauthenticated_user_cannot_manage_forms(): void
    {
        $response = $this->postJson('/api/core/admin/ja/forms', [
            'name' => 'Test Form',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test user without permission cannot manage forms.
     */
    public function test_user_without_permission_cannot_manage_forms(): void
    {
        $user = $this->createUser();
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/core/admin/ja/forms', [
            'name' => 'Test Form',
        ]);

        $response->assertStatus(403);
    }
}
