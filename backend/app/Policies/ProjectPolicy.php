<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        return (int) $user->id === (int) $project->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Project $project): bool
    {
        return (int) $user->id === (int) $project->user_id;
    }

    public function delete(User $user, Project $project): bool
    {
        return (int) $user->id === (int) $project->user_id;
    }

    public function duplicate(User $user, Project $project): bool
    {
        return (int) $user->id === (int) $project->user_id;
    }

    public function generate(User $user, Project $project): bool
    {
        return (int) $user->id === (int) $project->user_id;
    }

    public function publish(User $user, Project $project): bool
    {
        return (int) $user->id === (int) $project->user_id;
    }
}
