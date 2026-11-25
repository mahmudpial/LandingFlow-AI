<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Enums\GenerationStatus;
use App\Models\Generation;
use App\Models\Project;
use App\Models\User;
use App\Services\Projects\ActivityLogService;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

class AIGenerationService
{
    public function __construct(
        private readonly AIProviderInterface $provider,
        private readonly ActivityLogService $activityLog,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     */
    public function generateForProject(User $user, Project $project, array $input): Generation
    {
        $this->enforceHourlyQuota($user);

        try {
            $result = $this->provider->generateLandingPage($input);

            return DB::transaction(function () use ($user, $project, $input, $result): Generation {
                $generation = Generation::create([
                    'user_id' => $user->id,
                    'project_id' => $project->id,
                    'provider' => $result->provider,
                    'model_id' => $result->modelId,
                    'status' => GenerationStatus::Completed->value,
                    'input_payload' => $input,
                    'output_payload' => $result->content,
                    'prompt_tokens' => $result->promptTokens,
                    'completion_tokens' => $result->completionTokens,
                    'duration_ms' => $result->durationMs,
                ]);

                $project->update([
                    'business_info' => $input,
                    'content' => $result->content,
                    'seo_title' => $result->content['seo']['title'] ?? $project->name,
                    'seo_description' => $result->content['seo']['description'] ?? $project->description,
                ]);

                $user->increment('ai_generations_count');

                $this->activityLog->log(
                    user: $user,
                    action: 'ai.generated',
                    description: "Generated AI landing page content for \"{$project->name}\"",
                    project: $project,
                    metadata: [
                        'generation_uuid' => $generation->uuid,
                        'provider' => $result->provider,
                        'model_id' => $result->modelId,
                        'duration_ms' => $result->durationMs,
                    ]
                );

                return $generation;
            });
        } catch (Throwable $e) {
            Generation::create([
                'user_id' => $user->id,
                'project_id' => $project->id,
                'provider' => $this->provider->getProviderName(),
                'model_id' => $this->provider->getModelId(),
                'status' => GenerationStatus::Failed->value,
                'input_payload' => $input,
                'error_message' => mb_substr($e->getMessage(), 0, 500),
            ]);

            throw $e;
        }
    }

    private function enforceHourlyQuota(User $user): void
    {
        $hourlyLimit = max(1, (int) config('ai.rate_limit_per_hour', 15));

        $recentCount = Generation::query()
            ->where('user_id', $user->id)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($recentCount >= $hourlyLimit) {
            throw new TooManyRequestsHttpException(
                3600,
                "Hourly AI generation limit ({$hourlyLimit} per hour) reached. Please wait before generating again."
            );
        }
    }
}
