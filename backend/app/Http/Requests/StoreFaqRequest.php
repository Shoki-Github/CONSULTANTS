<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manageContent') ?? false;
    }

    public function rules(): array
    {
        return [
            'question' => ['sometimes', 'required', 'string', 'max:255'],
            'answer' => ['sometimes', 'required', 'string', 'max:10000'],
            'is_active' => ['sometimes', 'required', 'boolean'],
            'sort_order' => ['sometimes', 'required', 'integer', 'min:0', 'max:65535'],
        ];
    }
}
