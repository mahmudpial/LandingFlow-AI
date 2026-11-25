<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\GenerateLandingPageRequest;
use App\Http\Requests\Project\PublishProjectRequest;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectContentRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\GenerationResource;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\Template;
use App\Models\User;
use App\Services\AI\AIGenerationService;
use App\Services\Projects\ProjectService;
use App\Services\Publishing\PublishingService;
use App\Services\Templates\TemplateService;
use App\Support\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ProjectService $projectService,
        private readonly AIGenerationService $aiGenerationService,
        private readonly PublishingService $publishingService,
        private readonly TemplateService $templateService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $paginator = $this->projectService->listForUser($user, $request->only([
            'search',
            'status',
            'sort',
            'per_page',
        ]));

        return ApiResponse::success(
            ProjectResource::collection($paginator->items()),
            'Projects retrieved successfully.',
            200,
            [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        );
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $project = $this->projectService->createProject($user, $request->validated());

        return ApiResponse::success(
            new ProjectResource($project),
            'Project created successfully.',
            201
        );
    }

    public function show(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $project->load(['template', 'publishedPage', 'generations']);

        return ApiResponse::success(
            new ProjectResource($project),
            'Project details retrieved.'
        );
    }

    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        /** @var User $user */
        $user = $request->user();

        $updated = $this->projectService->updateProject($user, $project, $request->validated());

        return ApiResponse::success(
            new ProjectResource($updated),
            'Project updated successfully.'
        );
    }

    public function destroy(Request $request, Project $project): JsonResponse
    {
        $this->authorize('delete', $project);

        /** @var User $user */
        $user = $request->user();

        $this->projectService->deleteProject($user, $project);

        return ApiResponse::success(null, 'Project deleted successfully.');
    }

    public function duplicate(Request $request, Project $project): JsonResponse
    {
        $this->authorize('duplicate', $project);

        /** @var User $user */
        $user = $request->user();

        $copy = $this->projectService->duplicateProject($user, $project);

        return ApiResponse::success(
            new ProjectResource($copy),
            'Project duplicated successfully.',
            201
        );
    }

    public function updateContent(UpdateProjectContentRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        /** @var User $user */
        $user = $request->user();

        $updated = $this->projectService->updateContentAndTheme($user, $project, $request->validated());

        return ApiResponse::success(
            new ProjectResource($updated),
            'Landing page content saved successfully.'
        );
    }

    public function generate(GenerateLandingPageRequest $request, Project $project): JsonResponse
    {
        $this->authorize('generate', $project);

        /** @var User $user */
        $user = $request->user();

        $generation = $this->aiGenerationService->generateForProject(
            $user,
            $project,
            $request->validated()
        );

        return ApiResponse::success(
            [
                'generation' => new GenerationResource($generation),
                'project' => new ProjectResource($project->fresh(['template', 'publishedPage', 'generations'])),
            ],
            'AI landing page content generated successfully.'
        );
    }

    public function applyTemplate(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'template_id' => ['required', 'integer', 'exists:templates,id'],
            'replace_content' => ['sometimes', 'boolean'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $template = Template::findOrFail($validated['template_id']);

        $updated = $this->templateService->applyTemplateToProject(
            $user,
            $project,
            $template,
            (bool) ($validated['replace_content'] ?? false)
        );

        return ApiResponse::success(
            new ProjectResource($updated),
            "Applied template \"{$template->name}\" successfully."
        );
    }

    public function publish(PublishProjectRequest $request, Project $project): JsonResponse
    {
        $this->authorize('publish', $project);

        /** @var User $user */
        $user = $request->user();
        $shouldPublish = (bool) $request->validated('publish');

        if ($shouldPublish) {
            $this->publishingService->publish(
                $user,
                $project,
                $request->validated('slug')
            );

            return ApiResponse::success(
                new ProjectResource($project->fresh(['template', 'publishedPage'])),
                'Landing page published successfully.'
            );
        }

        $unpublished = $this->publishingService->unpublish($user, $project);

        return ApiResponse::success(
            new ProjectResource($unpublished),
            'Landing page unpublished and reverted to draft.'
        );
    }
}
