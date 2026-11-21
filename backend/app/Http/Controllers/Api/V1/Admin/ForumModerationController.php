<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ForumPost;
use App\Models\ForumReport;
use App\Models\ForumTopic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ForumModerationController extends Controller
{
    public function pending(): JsonResponse
    {
        $topics = ForumTopic::query()
            ->where('status', 'pending')
            ->with(['user:id,name', 'category:id,name'])
            ->latest()
            ->paginate(50);
        $posts = ForumPost::query()
            ->where('status', 'pending')
            ->with(['user:id,name', 'topic:id,title'])
            ->latest()
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => [
                'topics' => $topics,
                'posts' => $posts,
            ],
        ]);
    }

    public function reports(): JsonResponse
    {
        $reports = ForumReport::query()
            ->where('status', 'open')
            ->with(['topic:id,title', 'reporter:id,name'])
            ->latest()
            ->paginate(50);

        return response()->json(['success' => true, 'data' => $reports]);
    }

    public function updateTopic(Request $request, ForumTopic $topic): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'published', 'hidden'])],
        ]);
        DB::transaction(function () use ($request, $topic, $data): void {
            $topic->update($data);
            $this->audit($request, 'forum_topic.moderated', $topic, $data);
        });

        return response()->json(['success' => true, 'data' => $topic->only(['id', 'status'])]);
    }

    public function updatePost(Request $request, ForumPost $post): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'published', 'hidden'])],
        ]);
        DB::transaction(function () use ($request, $post, $data): void {
            $post->update($data);
            $this->audit($request, 'forum_post.moderated', $post, $data);
        });

        return response()->json(['success' => true, 'data' => $post->only(['id', 'status'])]);
    }

    public function updateReport(Request $request, ForumReport $report): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['reviewed', 'dismissed'])],
        ]);
        DB::transaction(function () use ($request, $report, $data): void {
            $report->update($data);
            $this->audit($request, 'forum_report.reviewed', $report, $data);
        });

        return response()->json(['success' => true, 'data' => $report->only(['id', 'status'])]);
    }

    private function audit(Request $request, string $action, ForumTopic|ForumPost|ForumReport $record, array $properties): void
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
