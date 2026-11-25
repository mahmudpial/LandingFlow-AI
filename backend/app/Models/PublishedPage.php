<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublishedPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'slug',
        'is_published',
        'published_snapshot',
        'seo_title',
        'seo_description',
        'view_count',
        'published_at',
        'unpublished_at',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_snapshot' => 'array',
            'view_count' => 'integer',
            'published_at' => 'datetime',
            'unpublished_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
