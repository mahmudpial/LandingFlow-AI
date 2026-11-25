<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\MediaAsset;
use App\Models\Project;
use App\Policies\MediaAssetPolicy;
use App\Policies\ProjectPolicy;
use App\Services\AI\AIProviderInterface;
use App\Services\AI\BedrockProvider;
use App\Services\AI\LandingPageSchemaValidator;
use App\Services\AI\MockAIProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LandingPageSchemaValidator::class);

        $this->app->bind(AIProviderInterface::class, function ($app): AIProviderInterface {
            $provider = (string) config('ai.provider', 'mock');

            return match ($provider) {
                'bedrock' => $app->make(BedrockProvider::class),
                default => $app->make(MockAIProvider::class),
            };
        });
    }

    public function boot(): void
    {
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(MediaAsset::class, MediaAssetPolicy::class);

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('auth', function (Request $request) {
            $email = strtolower((string) $request->input('email', ''));

            return Limit::perMinute(10)->by($email.'|'.$request->ip());
        });

        RateLimiter::for('ai-generation', function (Request $request) {
            $hourlyLimit = max(1, (int) config('ai.rate_limit_per_hour', 15));

            return Limit::perHour($hourlyLimit)->by($request->user()?->id ?: $request->ip());
        });
    }
}
