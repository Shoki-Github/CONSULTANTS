<?php

namespace App\Http\Requests;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manageContent') ?? false;
    }

    public function rules(): array
    {
        $service = $this->route('service');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'required',
                'alpha_dash:ascii',
                'max:255',
                Rule::unique('services', 'slug')->ignore($service instanceof Service ? $service->id : null),
            ],
            'summary' => ['sometimes', 'nullable', 'string', 'max:500'],
            'description' => ['sometimes', 'required', 'string', 'max:10000'],
            'is_active' => ['sometimes', 'required', 'boolean'],
            'sort_order' => ['sometimes', 'required', 'integer', 'min:0', 'max:65535'],
        ];
    }
}
