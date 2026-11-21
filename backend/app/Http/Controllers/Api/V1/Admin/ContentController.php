<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFaqRequest;
use App\Http\Requests\StoreNewsArticleRequest;
use App\Http\Requests\StoreServiceRequest;
use App\Models\AuditLog;
use App\Models\Faq;
use App\Models\NewsArticle;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContentController extends Controller
{
    public function services(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Service::query()->orderBy('sort_order')->orderBy('name')->paginate(50),
        ]);
    }

    public function storeService(StoreServiceRequest $request): JsonResponse
    {
        $service = DB::transaction(function () use ($request): Service {
            $service = Service::query()->create($request->validated());
            $this->audit($request, 'service.created', $service, ['slug' => $service->slug]);

            return $service;
        });

        return response()->json(['success' => true, 'data' => $service], 201);
    }

    public function updateService(StoreServiceRequest $request, Service $service): JsonResponse
    {
        DB::transaction(function () use ($request, $service): void {
            $service->fill($request->validated())->save();
            $this->audit($request, 'service.updated', $service, [
                'slug' => $service->slug,
                'is_active' => $service->is_active,
            ]);
        });

        return response()->json(['success' => true, 'data' => $service]);
    }

    public function news(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => NewsArticle::query()->latest()->paginate(50),
        ]);
    }

    public function storeNews(StoreNewsArticleRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (($data['status'] ?? 'draft') === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }
        $data['author_id'] = $request->user()->id;
        $article = DB::transaction(function () use ($request, $data): NewsArticle {
            $article = NewsArticle::query()->create($data);
            $this->audit($request, 'news_article.created', $article, [
                'slug' => $article->slug,
                'status' => $article->status,
            ]);

            return $article;
        });

        return response()->json(['success' => true, 'data' => $article], 201);
    }

    public function updateNews(StoreNewsArticleRequest $request, NewsArticle $article): JsonResponse
    {
        $data = $request->validated();
        if (($data['status'] ?? null) === 'published' && empty($data['published_at'])) {
            $data['published_at'] = $article->published_at ?? now();
        }
        DB::transaction(function () use ($request, $article, $data): void {
            $article->fill($data)->save();
            $this->audit($request, 'news_article.updated', $article, [
                'slug' => $article->slug,
                'status' => $article->status,
            ]);
        });

        return response()->json(['success' => true, 'data' => $article]);
    }

    public function faqs(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Faq::query()->orderBy('sort_order')->paginate(50),
        ]);
    }

    public function storeFaq(StoreFaqRequest $request): JsonResponse
    {
        $faq = DB::transaction(function () use ($request): Faq {
            $faq = Faq::query()->create($request->validated());
            $this->audit($request, 'faq.created', $faq, ['is_active' => $faq->is_active]);

            return $faq;
        });

        return response()->json(['success' => true, 'data' => $faq], 201);
    }

    public function updateFaq(StoreFaqRequest $request, Faq $faq): JsonResponse
    {
        DB::transaction(function () use ($request, $faq): void {
            $faq->fill($request->validated())->save();
            $this->audit($request, 'faq.updated', $faq, ['is_active' => $faq->is_active]);
        });

        return response()->json(['success' => true, 'data' => $faq]);
    }

    private function audit(Request $request, string $action, Service|NewsArticle|Faq $record, array $properties): void
    {
        AuditLog::query()->create([
            'user_id' => $request->user()->id,
            'action' => $action,
            'auditable_type' => $record::class,
            'auditable_id' => $record->id,
            'properties' => $properties,
            'ip_address' => $request->ip(),
        ]);
    }
}
