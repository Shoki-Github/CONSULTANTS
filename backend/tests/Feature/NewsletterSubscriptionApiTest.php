<?php

namespace Tests\Feature;

use App\Models\NewsletterSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterSubscriptionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitor_can_subscribe_with_consent(): void
    {
        $this->postJson('/api/v1/newsletter-subscriptions', [
            'email' => '  Reader@Example.org ',
            'name' => 'Asha Reader',
            'organisation' => 'Example NGO',
            'source' => 'home',
            'consent' => true,
        ])->assertCreated()->assertJsonPath('success', true);

        $subscriber = NewsletterSubscriber::query()->sole();
        $this->assertSame('reader@example.org', $subscriber->email);
        $this->assertSame('home', $subscriber->source);
        $this->assertNotNull($subscriber->consented_at);
        $this->assertNull($subscriber->unsubscribed_at);
    }

    public function test_subscription_requires_consent_and_valid_email(): void
    {
        $this->postJson('/api/v1/newsletter-subscriptions', [
            'email' => 'not-an-email',
        ])->assertUnprocessable()->assertJsonValidationErrors(['email', 'consent']);

        $this->assertDatabaseCount('newsletter_subscribers', 0);
    }

    public function test_honeypot_submissions_are_rejected(): void
    {
        $this->postJson('/api/v1/newsletter-subscriptions', [
            'email' => 'bot@example.org',
            'consent' => true,
            'company_website' => 'https://spam.example',
        ])->assertUnprocessable()->assertJsonValidationErrors(['company_website']);

        $this->assertDatabaseCount('newsletter_subscribers', 0);
    }

    public function test_existing_address_gets_same_response_and_is_resubscribed(): void
    {
        $existing = NewsletterSubscriber::factory()->unsubscribed()->create([
            'email' => 'reader@example.org',
            'name' => 'Original Name',
            'source' => 'toolkit',
        ]);

        $this->postJson('/api/v1/newsletter-subscriptions', [
            'email' => 'reader@example.org',
            'source' => 'home',
            'consent' => true,
        ])->assertCreated()->assertJsonPath('message', 'Thank you for subscribing.');

        $existing->refresh();
        $this->assertDatabaseCount('newsletter_subscribers', 1);
        $this->assertNull($existing->unsubscribed_at);
        $this->assertSame('Original Name', $existing->name);
        $this->assertSame('toolkit', $existing->source);
    }
}
