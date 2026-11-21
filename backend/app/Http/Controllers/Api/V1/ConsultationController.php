<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConsultationRequest;
use App\Models\Consultation;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ConsultationController extends Controller
{
    public function store(StoreConsultationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $serviceIds = Service::query()
            ->where('is_active', true)
            ->whereIn('id', $data['service_ids'])
            ->pluck('id')
            ->all();

        abort_unless(count($serviceIds) === count($data['service_ids']), 422, 'One or more selected services are unavailable.');

        $consultation = DB::transaction(function () use ($data, $serviceIds): Consultation {
            $consultation = Consultation::query()->create([
                'reference' => (string) Str::uuid(),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'message' => $data['message'],
            ]);
            $consultation->services()->sync($serviceIds);

            return $consultation;
        });

        return response()->json([
            'success' => true,
            'message' => 'Consultation request received.',
            'data' => ['reference' => $consultation->reference],
        ], 201);
    }
}
