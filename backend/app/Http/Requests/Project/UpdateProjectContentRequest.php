<?php

declare(strict_types=1);

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'content' => ['sometimes', 'required', 'array'],
            'content.sections' => ['required_with:content', 'array'],
            'content.sections_order' => ['sometimes', 'array'],
            'content.seo' => ['sometimes', 'array'],
            'theme' => ['sometimes', 'array'],
            'theme.colors' => ['sometimes', 'array'],
            'theme.typography' => ['sometimes', 'array'],
            'seo_title' => ['nullable', 'string', 'max:160'],
            'seo_description' => ['nullable', 'string', 'max:320'],
        ];
    }
}
