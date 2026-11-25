<?php

declare(strict_types=1);

namespace App\Services\Projects;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\User;

class ActivityLogService
{
    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function log(
        User $user,
        string $action,
        string $description,
        ?Project $project = null,
        ?array $metadata = null
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => $user->id,
            'project_id' => $project?->id,
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
}
