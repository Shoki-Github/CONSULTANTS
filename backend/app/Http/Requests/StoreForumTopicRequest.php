<?php

namespace App\Http\Requests;

use App\Models\ForumTopic;
use Illuminate\Foundation\Http\FormRequest;

class StoreForumTopicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ForumTopic::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:forum_categories,id'],
            'title' => ['required', 'string', 'min:8', 'max:180'],
            'body' => ['required', 'string', 'min:20', 'max:10000'],
        ];
    }
}
