<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Consultation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ConsultationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $consultations = Consultation::query()
            ->with(['services:id,name', 'assignee:id,name'])
            ->latest()
            ->paginate(min(max($request->integer('per_page', 20), 1), 50));

        return response()->json(['success' => true, 'data' => $consultations]);
    }

    public function update(Request $request, Consultation $consultation): JsonResponse
    {
        $data = $request->validate([
            'status' => ['sometimes', 'required', Rule::in(['new', 'reviewing', 'contacted', 'closed'])],
            'assigned_to' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
        ]);

        DB::transaction(function () use ($request, $consultation, $data): void {
            $consultation->fill($data)->save();
            AuditLog::query()->create([
                'user_id' => $request->user()->id,
                'action' => 'consultation.updated',
                'auditable_type' => Consultation::class,
                'auditable_id' => $consultation->id,
                'properties' => $data,
                'ip_address' => $request->ip(),
            ]);
        });

        return response()->json([
            'success' => true,
            'data' => $consultation->only(['id', 'reference', 'status', 'assigned_to']),
        ]);
    }
}
