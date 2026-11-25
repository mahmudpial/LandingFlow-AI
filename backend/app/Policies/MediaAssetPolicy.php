<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MediaAsset;
use App\Models\User;

class MediaAssetPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, MediaAsset $mediaAsset): bool
    {
        return (int) $user->id === (int) $mediaAsset->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function delete(User $user, MediaAsset $mediaAsset): bool
    {
        return (int) $user->id === (int) $mediaAsset->user_id;
    }
}
