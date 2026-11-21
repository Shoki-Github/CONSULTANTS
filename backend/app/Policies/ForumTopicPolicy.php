<?php

namespace App\Policies;

use App\Models\ForumTopic;
use App\Models\User;

class ForumTopicPolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermission('create_forum_posts');
    }

    public function view(User $user, ForumTopic $topic): bool
    {
        return $topic->status === 'published'
            || $topic->user_id === $user->id
            || $user->hasPermission('moderate_forum');
    }

    public function reply(User $user, ForumTopic $topic): bool
    {
        return $topic->status === 'published'
            && $topic->category->is_active
            && $this->create($user);
    }
}
