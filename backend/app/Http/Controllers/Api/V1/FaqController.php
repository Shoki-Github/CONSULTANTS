<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;

class FaqController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Faq::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(['question', 'answer']),
        ]);
    }
}
