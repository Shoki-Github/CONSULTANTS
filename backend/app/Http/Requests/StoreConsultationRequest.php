<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreConsultationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'message' => ['required', 'string', 'min:20', 'max:5000'],
            'service_ids' => ['required', 'array', 'min:1', 'max:10'],
            'service_ids.*' => ['required', 'integer', 'distinct', 'exists:services,id'],
            'company_website' => ['nullable', 'prohibited'],
        ];
    }
}
