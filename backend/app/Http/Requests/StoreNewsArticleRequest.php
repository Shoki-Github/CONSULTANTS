<?php

namespace App\Http\Requests;

use App\Models\NewsArticle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreNewsArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manageContent') ?? false;
    }

    public function rules(): array
    {
        $article = $this->route('article');

        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'required',
                'alpha_dash:ascii',
                'max:255',
                Rule::unique('news_articles', 'slug')->ignore($article instanceof NewsArticle ? $article->id : null),
            ],
            'excerpt' => ['sometimes', 'nullable', 'string', 'max:500'],
            'category' => ['sometimes', 'nullable', Rule::in(['MEL', 'Data & AI', 'ESG', 'Dira 2050', 'Training'])],
            'body' => ['sometimes', 'required', 'string', 'max:100000'],
            'status' => ['sometimes', 'required', Rule::in(['draft', 'published', 'archived'])],
            'published_at' => ['sometimes', 'nullable', 'date'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $article = $this->route('article');
                $status = $this->input('status', $article instanceof NewsArticle ? $article->status : 'draft');
                $category = $this->input('category', $article instanceof NewsArticle ? $article->category : null);

                if ($status === 'published' && ! $category) {
                    $validator->errors()->add('category', 'Select a category before publishing.');
                }
            },
        ];
    }
}
