<?php

namespace Tests\Feature;

use App\Models\ForumCategory;
use App\Models\ForumTopic;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ForumApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_cannot_create_forum_topic(): void
    {
        $category = ForumCategory::query()->firstOrFail();

        $this->postJson('/api/v1/forum/topics', [
            'category_id' => $category->id,
            'title' => 'A meaningful discussion',
            'body' => 'This is a sufficiently detailed discussion prompt.',
        ])->assertUnauthorized();
    }

    public function test_registration_creates_a_member_without_exposing_credentials(): void
    {
        $this->withHeader('Origin', 'http://localhost:5173')
            ->postJson('/api/v1/auth/register', [
                'name' => 'Jordan Example',
                'email' => 'jordan@example.test',
                'password' => 'A-strong-password-2026',
                'password_confirmation' => 'A-strong-password-2026',
            ])
            ->assertCreated()
            ->assertJsonPath('data.email', 'jordan@example.test')
            ->assertJsonMissingPath('data.password');

        $user = User::query()->where('email', 'jordan@example.test')->firstOrFail();
        $this->assertTrue($user->hasRole('registered_member'));
        $this->assertDatabaseMissing('users', ['password' => 'A-strong-password-2026']);
    }

    public function test_registered_member_topic_is_queued_for_moderation(): void
    {
        $member = User::factory()->create();
        $member->roles()->attach(Role::query()->where('name', 'registered_member')->firstOrFail());
        $category = ForumCategory::query()->firstOrFail();
        Sanctum::actingAs($member);

        $response = $this->postJson('/api/v1/forum/topics', [
            'category_id' => $category->id,
            'title' => 'A meaningful discussion',
            'body' => 'This is a sufficiently detailed discussion prompt.',
        ])->assertAccepted()
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('forum_topics', [
            'id' => $response->json('data.id'),
            'user_id' => $member->id,
            'status' => 'pending',
        ]);
    }

    public function test_member_cannot_access_administrative_consultations(): void
    {
        $member = User::factory()->create();
        $member->roles()->attach(Role::query()->where('name', 'registered_member')->firstOrFail());
        Sanctum::actingAs($member);

        $this->getJson('/api/v1/admin/consultations')->assertForbidden();
    }

    public function test_public_topic_list_excludes_unpublished_topics(): void
    {
        $member = User::factory()->create();
        $category = ForumCategory::query()->firstOrFail();
        ForumTopic::query()->create([
            'category_id' => $category->id,
            'user_id' => $member->id,
            'title' => 'Pending topic',
            'body' => 'This is not yet visible to visitors.',
            'status' => 'pending',
        ]);

        $this->getJson('/api/v1/forum/topics')
            ->assertOk()
            ->assertJsonPath('data.data', []);
    }

    public function test_public_topic_response_omits_internal_user_and_moderation_fields(): void
    {
        $member = User::factory()->create();
        $category = ForumCategory::query()->firstOrFail();
        ForumTopic::query()->create([
            'category_id' => $category->id,
            'user_id' => $member->id,
            'title' => 'Published topic',
            'body' => 'A question for the community.',
            'status' => 'published',
        ]);

        $this->getJson('/api/v1/forum/topics')
            ->assertOk()
            ->assertJsonPath('data.data.0.title', 'Published topic')
            ->assertJsonMissingPath('data.data.0.user_id')
            ->assertJsonMissingPath('data.data.0.status')
            ->assertJsonMissingPath('data.data.0.user.id');
    }
}
