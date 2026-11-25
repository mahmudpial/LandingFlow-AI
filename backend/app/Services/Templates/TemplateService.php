<?php

declare(strict_types=1);

namespace App\Services\Templates;

use App\Models\Project;
use App\Models\Template;
use App\Models\User;
use App\Services\AI\LandingPageSchemaValidator;
use App\Services\Projects\ActivityLogService;
use Illuminate\Database\Eloquent\Collection;

class TemplateService
{
    public function __construct(
        private readonly LandingPageSchemaValidator $validator,
        private readonly ActivityLogService $activityLog,
    ) {}

    /**
     * @return Collection<int, Template>
     */
    public function listActiveTemplates(): Collection
    {
        return Template::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get();
    }

    /**
     * Apply a template to an existing project without wiping out user's customized copy.
     */
    public function applyTemplateToProject(User $user, Project $project, Template $template, bool $replaceContent = false): Project
    {
        $newTheme = [
            'template_slug' => $template->slug,
            'layout_variant' => $template->category,
            'colors' => $template->color_palette,
            'typography' => $template->typography,
        ];

        $content = $project->content;
        if ($replaceContent || empty($content) || empty($content['sections'])) {
            $content = $this->validator->validateAndNormalize($template->default_schema, $project->name);
        }

        $project->update([
            'template_id' => $template->id,
            'theme' => $newTheme,
            'content' => $content,
        ]);

        $this->activityLog->log(
            user: $user,
            action: 'template.applied',
            description: "Applied template \"{$template->name}\" to \"{$project->name}\"",
            project: $project,
            metadata: ['template_slug' => $template->slug]
        );

        return $project->fresh(['template', 'publishedPage']);
    }
}
