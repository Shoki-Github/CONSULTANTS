<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNewsletterSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => mb_strtolower(trim($this->input('email')))]);
        }
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'name' => ['nullable', 'string', 'max:200'],
            'organisation' => ['nullable', 'string', 'max:200'],
            'source' => ['nullable', 'string', Rule::in(['home', 'insights', 'toolkit'])],
            'consent' => ['accepted'],
            'company_website' => ['nullable', 'prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'consent.accepted' => 'Please confirm that you agree to receive our updates.',
        ];
    }
}
