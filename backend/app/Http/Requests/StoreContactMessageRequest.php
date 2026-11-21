<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'organisation' => ['required', 'string', 'max:200'],
            'organisation_type' => ['required', 'string', Rule::in([
                'Government',
                'Development partner',
                'NGO',
                'Private sector',
                'Individual',
            ])],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'service_of_interest' => ['required', 'string', 'max:200'],
            'preferred_language' => ['required', 'string', Rule::in(['English', 'Kiswahili'])],
            'subject' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'company_website' => ['nullable', 'prohibited'],
        ];
    }
}
