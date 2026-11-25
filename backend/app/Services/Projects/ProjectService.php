<?php

declare(strict_types=1);

namespace App\Services\Projects;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\Template;
use App\Models\User;
use App\Services\AI\LandingPageSchemaValidator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ProjectService
{
    public function __construct(
        private readonly LandingPageSchemaValidator $validator,
        private readonly ActivityLogService $activityLog,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function listForUser(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = Project::query()
            ->with(['template', 'publishedPage'])
            ->where('user_id', $user->id);

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status']) && in_array($filters['status'], ['draft', 'published', 'archived'], true)) {
            $query->where('status', $filters['status']);
        }

        $sort = (string) ($filters['sort'] ?? 'newest');
        match ($sort) {
            'oldest' => $query->orderBy('created_at', 'asc'),
            'name' => $query->orderBy('name', 'asc'),
            'updated' => $query->orderBy('updated_at', 'desc'),
            default => $query->orderBy('created_at', 'desc'),
        };

        $perPage = min(50, max(1, (int) ($filters['per_page'] ?? 12)));

        return $query->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createProject(User $user, array $data): Project
    {
        $template = null;
        if (! empty($data['template_id'])) {
            $template = Template::find($data['template_id']);
        }
        if (! $template) {
            $template = Template::query()->where('is_active', true)->first();
        }

        $name = trim((string) $data['name']);
        $slug = $this->generateUniqueSlug((string) ($data['slug'] ?? $name));

        $defaultTheme = [
            'template_slug' => $template?->slug ?? 'startup-saas',
            'layout_variant' => $template?->category ?? 'saas',
            'colors' => $template?->color_palette ?? [
                'primary' => '#635BFF',
                'secondary' => '#8B5CF6',
                'accent' => '#14B8A6',
                'background' => '#F8FAFC',
                'surface' => '#FFFFFF',
                'text' => '#0F172A',
            ],
            'typography' => $template?->typography ?? [
                'heading_font' => 'Inter',
                'body_font' => 'Inter',
                'scale' => 'comfortable',
            ],
        ];

        $starterSchema = $template?->default_schema ?? $this->buildFallbackStarterSchema($name);
        $validatedContent = $this->validator->validateAndNormalize($starterSchema, $name);

        $project = Project::create([
            'user_id' => $user->id,
            'template_id' => $template?->id,
            'name' => $name,
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'status' => ProjectStatus::Draft->value,
            'business_info' => $data['business_info'] ?? null,
            'content' => $validatedContent,
            'theme' => $defaultTheme,
            'seo_title' => $data['seo_title'] ?? $validatedContent['seo']['title'],
            'seo_description' => $data['seo_description'] ?? $validatedContent['seo']['description'],
        ]);

        $this->activityLog->log(
            user: $user,
            action: 'project.created',
            description: "Created project \"{$project->name}\"",
            project: $project
        );

        return $project->load(['template', 'publishedPage']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateProject(User $user, Project $project, array $data): Project
    {
        $updates = [];

        if (array_key_exists('name', $data)) {
            $updates['name'] = trim((string) $data['name']);
        }
        if (array_key_exists('description', $data)) {
            $updates['description'] = $data['description'];
        }
        if (! empty($data['slug']) && $data['slug'] !== $project->slug) {
            $updates['slug'] = $this->generateUniqueSlug((string) $data['slug'], $project->id);
        }
        if (array_key_exists('status', $data) && in_array($data['status'], ['draft', 'published', 'archived'], true)) {
            $updates['status'] = $data['status'];
        }
        if (array_key_exists('seo_title', $data)) {
            $updates['seo_title'] = $data['seo_title'];
        }
        if (array_key_exists('seo_description', $data)) {
            $updates['seo_description'] = $data['seo_description'];
        }
        if (! empty($data['template_id']) && (int) $data['template_id'] !== (int) $project->template_id) {
            $template = Template::find($data['template_id']);
            if ($template) {
                $updates['template_id'] = $template->id;
                $updates['theme'] = [
                    'template_slug' => $template->slug,
                    'layout_variant' => $template->category,
                    'colors' => $template->color_palette,
                    'typography' => $template->typography,
                ];
            }
        }

        $project->update($updates);

        $this->activityLog->log(
            user: $user,
            action: 'project.updated',
            description: "Updated settings for \"{$project->name}\"",
            project: $project
        );

        return $project->fresh(['template', 'publishedPage']);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function updateContentAndTheme(User $user, Project $project, array $payload): Project
    {
        $updates = [];

        if (isset($payload['content']) && is_array($payload['content'])) {
            $validatedContent = $this->validator->validateAndNormalize($payload['content'], $project->name);
            $updates['content'] = $validatedContent;
            $updates['seo_title'] = $validatedContent['seo']['title'] ?? $project->seo_title;
            $updates['seo_description'] = $validatedContent['seo']['description'] ?? $project->seo_description;
        }

        if (isset($payload['theme']) && is_array($payload['theme'])) {
            $currentTheme = is_array($project->theme) ? $project->theme : [];
            $updates['theme'] = array_replace_recursive($currentTheme, $payload['theme']);
        }

        if (array_key_exists('seo_title', $payload)) {
            $updates['seo_title'] = $this->validator->cleanText((string) $payload['seo_title'], 160);
        }

        if (array_key_exists('seo_description', $payload)) {
            $updates['seo_description'] = $this->validator->cleanText((string) $payload['seo_description'], 320);
        }

        $project->update($updates);

        $this->activityLog->log(
            user: $user,
            action: 'project.content_saved',
            description: "Saved landing page editor changes for \"{$project->name}\"",
            project: $project
        );

        return $project->fresh(['template', 'publishedPage']);
    }

    public function duplicateProject(User $user, Project $source): Project
    {
        $newName = "{$source->name} (Copy)";
        $newSlug = $this->generateUniqueSlug("{$source->slug}-copy");

        $copy = Project::create([
            'user_id' => $user->id,
            'template_id' => $source->template_id,
            'name' => $newName,
            'slug' => $newSlug,
            'description' => $source->description,
            'status' => ProjectStatus::Draft->value,
            'business_info' => $source->business_info,
            'content' => $source->content,
            'theme' => $source->theme,
            'seo_title' => $source->seo_title,
            'seo_description' => $source->seo_description,
        ]);

        $this->activityLog->log(
            user: $user,
            action: 'project.duplicated',
            description: "Duplicated \"{$source->name}\" as \"{$copy->name}\"",
            project: $copy
        );

        return $copy->load(['template', 'publishedPage']);
    }

    public function deleteProject(User $user, Project $project): void
    {
        $name = $project->name;
        $project->publishedPage()?->delete();
        $project->delete();

        $this->activityLog->log(
            user: $user,
            action: 'project.deleted',
            description: "Deleted project \"{$name}\""
        );
    }

    public function generateUniqueSlug(string $baseInput, ?int $ignoreProjectId = null): string
    {
        $baseSlug = Str::slug($baseInput);
        if ($baseSlug === '') {
            $baseSlug = 'landing-page';
        }
        $baseSlug = mb_substr($baseSlug, 0, 140);

        $candidate = $baseSlug;
        $counter = 1;

        while ($this->slugExists($candidate, $ignoreProjectId)) {
            $counter++;
            $candidate = "{$baseSlug}-{$counter}";
        }

        return $candidate;
    }

    private function slugExists(string $slug, ?int $ignoreProjectId = null): bool
    {
        $query = Project::withTrashed()->where('slug', $slug);
        if ($ignoreProjectId !== null) {
            $query->where('id', '!=', $ignoreProjectId);
        }

        return $query->exists();
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFallbackStarterSchema(string $brandName): array
    {
        return [
            'seo' => [
                'title' => "{$brandName} — Built with LandingFlow AI",
                'description' => "Welcome to {$brandName}. Customize your sections or run the AI Landing Page Generator.",
                'keywords' => [$brandName, 'SaaS', 'Landing Page'],
            ],
            'sections_order' => LandingPageSchemaValidator::SUPPORTED_SECTIONS,
            'sections' => [
                'hero' => [
                    'visible' => true,
                    'badge' => 'New Launch',
                    'headline' => "Build and Launch {$brandName} with Confidence",
                    'subheadline' => 'Use the AI Generator or customize every section directly in the visual editor.',
                    'primary_cta_text' => 'Get Started',
                    'primary_cta_url' => '#pricing',
                    'secondary_cta_text' => 'Learn More',
                    'secondary_cta_url' => '#features',
                    'highlights' => ['Responsive Design', 'Conversion Focused', 'Instant Publishing'],
                ],
                'features' => [
                    'visible' => true,
                    'title' => 'Designed for Speed and Impact',
                    'subtitle' => 'Everything you need to present your product clearly.',
                    'items' => [
                        ['icon' => 'Sparkles', 'title' => 'AI Copy Generation', 'description' => 'Generate tailored headlines, benefits, and FAQs in seconds.'],
                        ['icon' => 'Layers', 'title' => 'Modular Sections', 'description' => 'Reorder, toggle, and customize every section effortlessly.'],
                        ['icon' => 'Globe', 'title' => 'Instant Public URL', 'description' => 'Publish your page to a clean, shareable slug with one click.'],
                    ],
                ],
                'cta' => [
                    'visible' => true,
                    'headline' => "Ready to launch {$brandName}?",
                    'subheadline' => 'Start customizing your page now.',
                    'button_text' => 'Get Started',
                    'button_url' => '#hero',
                ],
                'footer' => [
                    'visible' => true,
                    'brand_name' => $brandName,
                    'tagline' => 'Powered by LandingFlow AI',
                ],
            ],
        ];
    }
}
