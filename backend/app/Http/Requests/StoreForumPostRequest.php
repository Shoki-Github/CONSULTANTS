<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreForumPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('reply', $this->route('topic')) ?? false;
    }

    public function rules(): array
    {
        return ['body' => ['required', 'string', 'min:2', 'max:10000']];
    }
}
