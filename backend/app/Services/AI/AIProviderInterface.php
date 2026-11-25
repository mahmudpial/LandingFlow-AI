<?php

declare(strict_types=1);

namespace App\Services\AI;

interface AIProviderInterface
{
    /**
     * Generate structured landing page content from validated wizard parameters.
     *
     * @param  array<string, mixed>  $input
     */
    public function generateLandingPage(array $input): AIProviderResult;

    public function getProviderName(): string;

    public function getModelId(): string;
}
