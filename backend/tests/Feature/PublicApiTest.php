<?php

namespace Tests\Feature;

use App\Mail\ContactInquiryReceived;
use App\Models\Consultation;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\NewsArticle;
use App\Models\Service;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PublicApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_public_content_endpoints_only_return_published_records(): void
    {
        Service::query()->create([
            'name' => 'Published service',
            'slug' => 'published-service',
            'summary' => 'Public description',
            'description' => 'A complete description.',
            'is_active' => true,
        ]);
        Service::query()->create([
            'name' => 'Draft service',
            'slug' => 'draft-service',
            'description' => 'Not public.',
            'is_active' => false,
        ]);
        NewsArticle::query()->create([
            'title' => 'Published insight',
            'slug' => 'published-insight',
            'body' => 'Public article content.',
            'category' => 'MEL',
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);
        NewsArticle::query()->create([
            'title' => 'Digital Solutions That Drive Results: Advertorial',
            'slug' => 'old-promotional-post',
            'body' => 'Promotional article content.',
            'category' => 'Data & AI',
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);
        NewsArticle::query()->create([
            'title' => 'Draft insight',
            'slug' => 'draft-insight',
            'body' => 'Private draft.',
            'status' => 'draft',
        ]);
        Faq::query()->create(['question' => 'Public question?', 'answer' => 'Public answer.', 'is_active' => true]);
        Faq::query()->create(['question' => 'Private question?', 'answer' => 'Draft answer.', 'is_active' => false]);

        $this->getJson('/api/v1/services')
            ->assertOk()
            ->assertJsonPath('data.data.0.name', 'Published service')
            ->assertJsonMissing(['name' => 'Draft service']);
        $this->getJson('/api/v1/news')
            ->assertOk()
            ->assertJsonPath('data.data.0.title', 'Published insight')
            ->assertJsonPath('data.data.0.author', 'Pantaleon Shoki')
            ->assertJsonPath('data.data.0.reading_time', 1)
            ->assertJsonMissing(['title' => 'Draft insight']);
        $this->getJson('/api/v1/news/old-promotional-post')->assertNotFound();
        $this->getJson('/api/v1/faqs')
            ->assertOk()
            ->assertJsonPath('data.0.question', 'Public question?')
            ->assertJsonMissing(['question' => 'Private question?']);
    }

    public function test_contact_message_is_validated_stored_and_returns_opaque_reference(): void
    {
        Mail::fake();

        $this->postJson('/api/v1/contact-messages', [
            'name' => 'Visitor',
            'organisation' => 'Example organisation',
            'organisation_type' => 'NGO',
            'email' => 'visitor@example.test',
            'service_of_interest' => 'MEL Systems & Evaluation',
            'preferred_language' => 'English',
            'subject' => 'A question',
            'message' => 'I would like to know more.',
        ])->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['reference']]);

        Mail::assertSent(ContactInquiryReceived::class);
        $this->assertDatabaseCount('contact_messages', 1);
        $this->assertDatabaseHas('contact_messages', [
            'organisation' => 'Example organisation',
            'service_of_interest' => 'MEL Systems & Evaluation',
        ]);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f-]{36}$/i',
            ContactMessage::query()->firstOrFail()->reference,
        );
    }

    public function test_invalid_contact_message_is_rejected_without_storage(): void
    {
        $this->postJson('/api/v1/contact-messages', [
            'name' => 'Visitor',
            'organisation' => 'Example organisation',
            'organisation_type' => 'NGO',
            'email' => 'not-an-email',
            'service_of_interest' => 'MEL Systems & Evaluation',
            'preferred_language' => 'English',
            'subject' => '',
            'message' => 'Too short',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'subject', 'message']);

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_consultation_submission_requires_active_service_and_stores_selected_service(): void
    {
        $service = Service::query()->create([
            'name' => 'Active service',
            'slug' => 'active-service',
            'description' => 'Available for consultation.',
            'is_active' => true,
        ]);

        $this->postJson('/api/v1/consultations', [
            'first_name' => 'Avery',
            'last_name' => 'Taylor',
            'email' => 'avery@example.test',
            'message' => 'I would like to discuss a project with your team.',
            'service_ids' => [$service->id],
        ])->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['reference']]);

        $this->assertDatabaseCount('consultations', 1);
        $this->assertDatabaseHas('consultation_service', [
            'consultation_id' => Consultation::query()->firstOrFail()->id,
            'service_id' => $service->id,
        ]);
    }

    public function test_consultation_cannot_target_inactive_service(): void
    {
        $service = Service::query()->create([
            'name' => 'Inactive service',
            'slug' => 'inactive-service',
            'description' => 'Not available.',
            'is_active' => false,
        ]);

        $this->postJson('/api/v1/consultations', [
            'first_name' => 'Avery',
            'last_name' => 'Taylor',
            'email' => 'avery@example.test',
            'message' => 'I would like to discuss a project with your team.',
            'service_ids' => [$service->id],
        ])->assertUnprocessable();

        $this->assertDatabaseCount('consultations', 0);
    }
}
