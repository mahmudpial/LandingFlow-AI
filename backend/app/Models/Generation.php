<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Generation extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'project_id',
        'provider',
        'model_id',
        'status',
        'input_payload',
        'output_payload',
        'error_message',
        'prompt_tokens',
        'completion_tokens',
        'duration_ms',
    ];

    protected function casts(): array
    {
        return [
            'input_payload' => 'array',
            'output_payload' => 'array',
            'prompt_tokens' => 'integer',
            'completion_tokens' => 'integer',
            'duration_ms' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Generation $generation): void {
            if (empty($generation->uuid)) {
                $generation->uuid = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
