<?php

declare(strict_types=1);

namespace App\Services\AI;

final readonly class AIProviderResult
{
    public function __construct(
        public string $provider,
        public string $modelId,
        public array $content,
        public int $promptTokens = 0,
        public int $completionTokens = 0,
        public int $durationMs = 0,
    ) {}
}
