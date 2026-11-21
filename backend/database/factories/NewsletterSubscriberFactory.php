<?php

namespace Database\Factories;

use App\Models\NewsletterSubscriber;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NewsletterSubscriber>
 */
class NewsletterSubscriberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'name' => fake()->name(),
            'organisation' => fake()->company(),
            'source' => 'home',
            'consented_at' => now(),
            'unsubscribed_at' => null,
        ];
    }

    /**
     * Indicate that the subscriber has unsubscribed.
     */
    public function unsubscribed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'unsubscribed_at' => now()->subDay(),
        ]);
    }
}
