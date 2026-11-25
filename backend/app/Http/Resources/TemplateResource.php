<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Template */
class TemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'preview_image_url' => $this->preview_image_url,
            'color_palette' => $this->color_palette,
            'typography' => $this->typography,
            'supported_sections' => $this->supported_sections,
            'default_schema' => $this->default_schema,
            'is_active' => $this->is_active,
        ];
    }
}
