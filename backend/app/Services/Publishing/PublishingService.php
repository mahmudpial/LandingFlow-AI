<?php

declare(strict_types=1);

namespace App\Services\Publishing;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\PublishedPage;
use App\Models\User;
use App\Services\AI\LandingPageSchemaValidator;
use App\Services\Projects\ActivityLogService;
use App\Services\Projects\ProjectService;
use Illuminate\Support\Facades\DB;

class PublishingService
{
    public function __construct(
        private readonly LandingPageSchemaValidator $validator,
        private readonly ProjectService $projectService,
        private readonly ActivityLogService $activityLog,
    ) {}

    public function publish(User $user, Project $project, ?string $requestedSlug = null): PublishedPage
    {
        $slug = $project->slug;
        if ($requestedSlug !== null && trim($requestedSlug) !== '' && trim($requestedSlug) !== $project->slug) {
            $slug = $this->projectService->generateUniqueSlug($requestedSlug, $project->id);
        }

        $validatedContent = $this->validator->validateAndNormalize(
            is_array($project->content) ? $project->content : [],
            $project->name
        );

        $snapshot = [
            'project_uuid' => $project->uuid,
            'project_name' => $project->name,
            'slug' => $slug,
            'template_slug' => $project->theme['template_slug'] ?? $project->template?->slug ?? 'startup-saas',
            'content' => $validatedContent,
            'theme' => $project->theme,
            'seo' => [
                'title' => $project->seo_title ?: ($validatedContent['seo']['title'] ?? $project->name),
                'description' => $project->seo_description ?: ($validatedContent['seo']['description'] ?? $project->description),
                'keywords' => $validatedContent['seo']['keywords'] ?? [],
            ],
            'published_at' => now()->toIso8601String(),
        ];

        return DB::transaction(function () use ($user, $project, $slug, $snapshot): PublishedPage {
            $project->update([
                'slug' => $slug,
                'status' => ProjectStatus::Published->value,
                'published_at' => now(),
            ]);

            $publishedPage = PublishedPage::updateOrCreate(
                ['project_id' => $project->id],
                [
                    'user_id' => $user->id,
                    'slug' => $slug,
                    'is_published' => true,
                    'published_snapshot' => $snapshot,
                    'seo_title' => $snapshot['seo']['title'],
                    'seo_description' => $snapshot['seo']['description'],
                    'published_at' => now(),
                    'unpublished_at' => null,
                ]
            );

            $this->activityLog->log(
                user: $user,
                action: 'page.published',
                description: "Published \"{$project->name}\" at /p/{$slug}",
                project: $project,
                metadata: ['slug' => $slug]
            );

            return $publishedPage;
        });
    }

    public function unpublish(User $user, Project $project): Project
    {
        return DB::transaction(function () use ($user, $project): Project {
            $project->update([
                'status' => ProjectStatus::Draft->value,
            ]);

            $project->publishedPage()?->update([
                'is_published' => false,
                'unpublished_at' => now(),
            ]);

            $this->activityLog->log(
                user: $user,
                action: 'page.unpublished',
                description: "Unpublished \"{$project->name}\"",
                project: $project
            );

            return $project->fresh(['template', 'publishedPage']);
        });
    }

    public function getPublicPageBySlug(string $slug): ?PublishedPage
    {
        $publishedPage = PublishedPage::query()
            ->with('project')
            ->where('slug', $slug)
            ->where('is_published', true)
            ->first();

        if (! $publishedPage || ! $publishedPage->project) {
            return null;
        }

        if ($publishedPage->project->status !== ProjectStatus::Published->value) {
            return null;
        }

        $publishedPage->increment('view_count');

        return $publishedPage;
    }
}
