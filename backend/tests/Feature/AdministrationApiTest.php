<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\ForumCategory;
use App\Models\ForumTopic;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdministrationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_content_manager_can_publish_a_service_and_the_change_is_audited(): void
    {
        $manager = User::factory()->create();
        $manager->roles()->attach(Role::query()->where('name', 'content_manager')->firstOrFail());
        Sanctum::actingAs($manager);

        $response = $this->postJson('/api/v1/admin/services', [
            'name' => 'Strategy support',
            'slug' => 'strategy-support',
            'summary' => 'A practical service overview.',
            'description' => 'Detailed information about this service.',
            'is_active' => true,
        ])->assertCreated()
            ->assertJsonPath('data.slug', 'strategy-support');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'service.created',
            'auditable_id' => $response->json('data.id'),
        ]);
        $this->getJson('/api/v1/services')->assertJsonPath('data.data.0.name', 'Strategy support');
    }

    public function test_registered_member_cannot_manage_website_content(): void
    {
        $member = User::factory()->create();
        $member->roles()->attach(Role::query()->where('name', 'registered_member')->firstOrFail());
        Sanctum::actingAs($member);

        $this->getJson('/api/v1/admin/services')->assertForbidden();
    }

    public function test_administrator_can_review_contact_message_and_the_change_is_audited(): void
    {
        $administrator = User::factory()->create();
        $administrator->roles()->attach(Role::query()->where('name', 'administrator')->firstOrFail());
        $message = ContactMessage::query()->create([
            'reference' => '6f8b1c6f-cbcb-4bdb-a7cc-7833b8bd4064',
            'name' => 'Visitor',
            'email' => 'visitor@example.test',
            'subject' => 'A question',
            'message' => 'I would like to know more about the services.',
        ]);
        Sanctum::actingAs($administrator);

        $this->patchJson("/api/v1/admin/contact-messages/{$message->id}", [
            'status' => 'responded',
        ])->assertOk()->assertJsonPath('data.status', 'responded');

        $this->assertDatabaseHas('contact_messages', ['id' => $message->id, 'status' => 'responded']);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'contact_message.updated',
            'auditable_id' => $message->id,
        ]);
    }

    public function test_forum_moderator_can_publish_pending_topic_and_action_is_audited(): void
    {
        $member = User::factory()->create();
        $member->roles()->attach(Role::query()->where('name', 'registered_member')->firstOrFail());
        $category = ForumCategory::query()->firstOrFail();
        $topic = ForumTopic::query()->create([
            'category_id' => $category->id,
            'user_id' => $member->id,
            'title' => 'Pending topic',
            'body' => 'This topic is awaiting a review.',
            'status' => 'pending',
        ]);

        $moderator = User::factory()->create();
        $moderator->roles()->attach(Role::query()->where('name', 'forum_moderator')->firstOrFail());
        Sanctum::actingAs($moderator);

        $this->patchJson("/api/v1/admin/forum/topics/{$topic->id}", [
            'status' => 'published',
        ])->assertOk()->assertJsonPath('data.status', 'published');

        $this->assertDatabaseHas('forum_topics', ['id' => $topic->id, 'status' => 'published']);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'forum_topic.moderated',
            'auditable_id' => $topic->id,
        ]);
    }
}
