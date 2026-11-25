<?php

declare(strict_types=1);

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class GenerateLandingPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'product_name' => ['required', 'string', 'min:2', 'max:120'],
            'product_description' => ['required', 'string', 'min:10', 'max:600'],
            'industry' => ['required', 'string', 'min:2', 'max:100'],
            'target_audience' => ['required', 'string', 'min:3', 'max:200'],
            'website_goal' => ['required', 'string', 'min:3', 'max:150'],
            'brand_tone' => ['required', 'string', 'min:2', 'max:80'],
            'language' => ['required', 'string', 'min:2', 'max:50'],
            'primary_cta' => ['required', 'string', 'min:2', 'max:60'],
            'keywords' => ['nullable', 'array', 'max:10'],
            'keywords.*' => ['string', 'max:50'],
        ];
    }
}
