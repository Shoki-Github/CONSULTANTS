<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $services = Service::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->select(['id', 'name', 'slug', 'summary'])
            ->paginate(min(max($request->integer('per_page', 12), 1), 50));

        return response()->json(['success' => true, 'data' => $services]);
    }

    public function show(Service $service): JsonResponse
    {
        abort_unless($service->is_active, 404);

        return response()->json([
            'success' => true,
            'data' => $service->only(['name', 'slug', 'summary', 'description']),
        ]);
    }
}
