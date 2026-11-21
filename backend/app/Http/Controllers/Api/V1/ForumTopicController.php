<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreForumPostRequest;
use App\Http\Requests\StoreForumTopicRequest;
use App\Models\ForumCategory;
use App\Models\ForumReport;
use App\Models\ForumTopic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ForumTopicController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $topics = ForumTopic::query()
            ->where('status', 'published')
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->with(['user:id,name', 'category:id,name,slug'])
            ->withCount(['posts' => fn ($query) => $query->where('status', 'published')])
            ->latest()
            ->paginate(min(max($request->integer('per_page', 15), 1), 50));
        $topics->getCollection()->transform(fn (ForumTopic $topic): array => [
            'id' => $topic->id,
            'title' => $topic->title,
            'body' => $topic->body,
            'created_at' => $topic->created_at,
            'user' => $topic->user->only(['name']),
            'category' => $topic->category->only(['name', 'slug']),
            'posts_count' => $topic->posts_count,
        ]);

        return response()->json(['success' => true, 'data' => $topics]);
    }

    public function show(ForumTopic $topic): JsonResponse
    {
        abort_unless($topic->status === 'published' && $topic->category->is_active, 404);
        $topic->load(['user:id,name', 'category:id,name,slug']);
        $posts = $topic->posts()
            ->where('status', 'published')
            ->with('user:id,name')
            ->oldest()
            ->paginate(30);
        $posts->getCollection()->transform(fn ($post): array => [
            'body' => $post->body,
            'created_at' => $post->created_at,
            'user' => $post->user->only(['name']),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'topic' => [
                    'id' => $topic->id,
                    'title' => $topic->title,
                    'body' => $topic->body,
                    'created_at' => $topic->created_at,
                    'user' => $topic->user->only(['name']),
                    'category' => $topic->category->only(['name', 'slug']),
                ],
                'posts' => $posts,
            ],
        ]);
    }

    public function store(StoreForumTopicRequest $request): JsonResponse
    {
        $data = $request->validated();
        abort_unless(ForumCategory::query()->whereKey($data['category_id'])->where('is_active', true)->exists(), 404);

        $topic = ForumTopic::query()->create([
            ...$data,
            'user_id' => $request->user()->id,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your topic was submitted for moderation.',
            'data' => ['id' => $topic->id, 'status' => $topic->status],
        ], 202);
    }

    public function reply(StoreForumPostRequest $request, ForumTopic $topic): JsonResponse
    {
        $post = $topic->posts()->create([
            'user_id' => $request->user()->id,
            'body' => $request->validated('body'),
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your reply was submitted for moderation.',
            'data' => ['id' => $post->id, 'status' => $post->status],
        ], 202);
    }

    public function report(Request $request, ForumTopic $topic): JsonResponse
    {
        abort_unless($topic->status === 'published', 404);
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
        ]);
        ForumReport::query()->create([
            'topic_id' => $topic->id,
            'reported_by' => $request->user()->id,
            'reason' => $data['reason'],
        ]);

        return response()->json(['success' => true, 'message' => 'Report submitted.'], 201);
    }
}
