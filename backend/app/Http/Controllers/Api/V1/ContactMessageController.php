<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactMessageRequest;
use App\Mail\ContactInquiryReceived;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class ContactMessageController extends Controller
{
    public function store(StoreContactMessageRequest $request): JsonResponse
    {
        $reference = (string) Str::uuid();
        $contactMessage = ContactMessage::query()->create([
            'reference' => $reference,
            ...$request->safe()->only([
                'name',
                'organisation',
                'organisation_type',
                'email',
                'phone',
                'service_of_interest',
                'preferred_language',
                'subject',
                'message',
            ]),
        ]);

        try {
            Mail::to('info@clmconsultants.co.tz')->send(new ContactInquiryReceived($contactMessage));
        } catch (Throwable $exception) {
            Log::error('Unable to deliver a consultation request email.', [
                'reference' => $reference,
                'exception' => $exception,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to send your request. Please email info@clmconsultants.co.tz.',
            ], 503);
        }

        return response()->json([
            'success' => true,
            'message' => 'Your consultation request has been received.',
            'data' => ['reference' => $reference],
        ], 201);
    }
}
