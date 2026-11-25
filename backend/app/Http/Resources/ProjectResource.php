<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Project */
class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'user_id' => $this->user_id,
            'template_id' => $this->template_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'status' => $this->status,
            'business_info' => $this->business_info,
            'content' => $this->content,
            'theme' => $this->theme,
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            'public_url' => "/p/{$this->slug}",
            'published_at' => $this->published_at?->toIso8601String(),
            'template' => new TemplateResource($this->whenLoaded('template')),
            'published_page' => $this->whenLoaded('publishedPage', fn () => $this->publishedPage ? [
                'id' => $this->publishedPage->id,
                'slug' => $this->publishedPage->slug,
                'is_published' => $this->publishedPage->is_published,
                'view_count' => $this->publishedPage->view_count,
                'published_at' => $this->publishedPage->published_at?->toIso8601String(),
            ] : null),
            'generations' => GenerationResource::collection($this->whenLoaded('generations')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
