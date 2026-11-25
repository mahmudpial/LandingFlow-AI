<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Template extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'category',
        'preview_image_url',
        'color_palette',
        'typography',
        'supported_sections',
        'default_schema',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'color_palette' => 'array',
            'typography' => 'array',
            'supported_sections' => 'array',
            'default_schema' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
