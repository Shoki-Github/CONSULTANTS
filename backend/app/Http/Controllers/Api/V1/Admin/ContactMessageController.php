<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ContactMessageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $messages = ContactMessage::query()
            ->latest()
            ->paginate(min(max($request->integer('per_page', 20), 1), 50));

        return response()->json(['success' => true, 'data' => $messages]);
    }

    public function update(Request $request, ContactMessage $contactMessage): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['new', 'reviewing', 'responded', 'closed'])],
        ]);

        DB::transaction(function () use ($request, $contactMessage, $data): void {
            $contactMessage->update($data);
            AuditLog::query()->create([
                'user_id' => $request->user()->id,
                'action' => 'contact_message.updated',
                'auditable_type' => ContactMessage::class,
                'auditable_id' => $contactMessage->id,
                'properties' => $data,
                'ip_address' => $request->ip(),
            ]);
        });

        return response()->json([
            'success' => true,
            'data' => $contactMessage->only(['id', 'reference', 'status']),
        ]);
    }
}
