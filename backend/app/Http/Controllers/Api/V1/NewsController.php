<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\NewsArticle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    private const UNPUBLISHED_PROMOTIONAL_PREFIXES = [
        'Digital Solutions That Drive Results',
        'From Monitoring to Impact',
        'Transforming Data into Decisions',
    ];

    public function index(Request $request): JsonResponse
    {
        $articles = NewsArticle::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereNotNull('category')
            ->where(function (Builder $query): void {
                foreach (self::UNPUBLISHED_PROMOTIONAL_PREFIXES as $prefix) {
                    $query->where('title', 'not like', $prefix.'%');
                }
            })
            ->orderByDesc('published_at')
            ->select(['title', 'slug', 'excerpt', 'category', 'body', 'published_at'])
            ->paginate(min(max($request->integer('per_page', 10), 1), 50))
            ->through(fn (NewsArticle $article): array => [
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt,
                'category' => $article->category,
                'published_at' => $article->published_at,
                'author' => 'Pantaleon Shoki',
                'reading_time' => $this->readingTime($article->body),
            ]);

        return response()->json(['success' => true, 'data' => $articles]);
    }

    public function show(NewsArticle $article): JsonResponse
    {
        $isPromotional = collect(self::UNPUBLISHED_PROMOTIONAL_PREFIXES)
            ->contains(fn (string $prefix) => Str::startsWith($article->title, $prefix));

        abort_unless(
            $article->status === 'published'
                && $article->published_at?->isPast()
                && $article->category
                && ! $isPromotional,
            404,
        );

        return response()->json([
            'success' => true,
            'data' => [
                ...$article->only(['title', 'slug', 'excerpt', 'category', 'body', 'published_at']),
                'author' => 'Pantaleon Shoki',
                'reading_time' => $this->readingTime($article->body),
            ],
        ]);
    }

    private function readingTime(string $body): int
    {
        preg_match_all('/\S+/u', strip_tags($body), $matches);

        return max(1, (int) ceil(count($matches[0]) / 200));
    }
}
