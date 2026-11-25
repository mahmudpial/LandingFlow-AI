<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\ProjectStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\ActivityLog;
use App\Models\Generation;
use App\Models\Project;
use App\Models\PublishedPage;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $totalProjects = Project::where('user_id', $user->id)->count();
        $publishedPages = Project::where('user_id', $user->id)
            ->where('status', ProjectStatus::Published->value)
            ->count();
        $draftProjects = Project::where('user_id', $user->id)
            ->where('status', ProjectStatus::Draft->value)
            ->count();

        $totalGenerations = Generation::where('user_id', $user->id)->count();
        $generationsLastHour = Generation::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        $totalPublicViews = (int) PublishedPage::where('user_id', $user->id)->sum('view_count');

        $recentProjects = Project::query()
            ->with(['template', 'publishedPage'])
            ->where('user_id', $user->id)
            ->latest('updated_at')
            ->limit(5)
            ->get();

        $recentActivity = ActivityLog::query()
            ->where('user_id', $user->id)
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (ActivityLog $log) => [
                'id' => $log->id,
                'action' => $log->action,
                'description' => $log->description,
                'project_id' => $log->project_id,
                'metadata' => $log->metadata,
                'created_at' => $log->created_at?->toIso8601String(),
            ]);

        return ApiResponse::success([
            'stats' => [
                'total_projects' => $totalProjects,
                'published_pages' => $publishedPages,
                'draft_projects' => $draftProjects,
                'ai_generations_used' => max((int) $user->ai_generations_count, $totalGenerations),
                'ai_generations_last_hour' => $generationsLastHour,
                'ai_hourly_limit' => (int) config('ai.rate_limit_per_hour', 15),
                'ai_provider' => (string) config('ai.provider', 'mock'),
                'total_public_views' => $totalPublicViews,
            ],
            'recent_projects' => ProjectResource::collection($recentProjects),
            'recent_activity' => $recentActivity,
        ], 'Dashboard data retrieved successfully.');
    }
}
