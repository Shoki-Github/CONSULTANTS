<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNewsletterSubscriptionRequest;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;

class NewsletterSubscriptionController extends Controller
{
    /**
     * Subscribe an address, or refresh consent for an existing one.
     *
     * The response is identical for new and existing addresses so the endpoint
     * cannot be used to discover who is on the list.
     */
    public function store(StoreNewsletterSubscriptionRequest $request): JsonResponse
    {
        $data = $request->safe()->only(['email', 'name', 'organisation', 'source']);

        $subscriber = NewsletterSubscriber::query()->firstOrNew(['email' => $data['email']]);
        $subscriber->fill(array_filter([
            'name' => $data['name'] ?? null,
            'organisation' => $data['organisation'] ?? null,
        ], fn ($value) => filled($value)));

        if (! $subscriber->exists) {
            $subscriber->source = $data['source'] ?? null;
        }

        $subscriber->consented_at = now();
        $subscriber->unsubscribed_at = null;
        $subscriber->save();

        return response()->json([
            'success' => true,
            'message' => 'Thank you for subscribing.',
        ], 201);
    }
}
