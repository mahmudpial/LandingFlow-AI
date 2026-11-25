<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Media\StoreMediaAssetRequest;
use App\Http\Resources\MediaAssetResource;
use App\Models\MediaAsset;
use App\Models\Project;
use App\Models\User;
use App\Services\Projects\ActivityLogService;
use App\Support\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaAssetController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ActivityLogService $activityLog,
    ) {}

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $assets = MediaAsset::query()
            ->where('user_id', $user->id)
            ->latest()
            ->limit(50)
            ->get();

        return ApiResponse::success(
            MediaAssetResource::collection($assets),
            'Media assets retrieved successfully.'
        );
    }

    public function store(StoreMediaAssetRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        /** @var UploadedFile $file */
        $file = $request->file('file');

        $projectId = $request->validated('project_id');
        $project = null;
        if ($projectId !== null) {
            $project = Project::findOrFail($projectId);
            $this->authorize('update', $project);
        }

        $mimeType = (string) $file->getMimeType();
        $extension = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };

        $disk = (string) config('filesystems.default', 'public');
        if ($disk === 'local') {
            $disk = 'public';
        }

        $filename = (string) Str::uuid() . '.' . $extension;
        $directory = 'media/' . $user->id;
        $path = $file->storeAs($directory, $filename, $disk);

        $dimensions = @getimagesize($file->getRealPath() ?: '');
        $width = is_array($dimensions) ? (int) ($dimensions[0] ?? 0) : null;
        $height = is_array($dimensions) ? (int) ($dimensions[1] ?? 0) : null;

        $sanitizedOriginal = preg_replace('/[^a-zA-Z0-9._-]/', '_', (string) $file->getClientOriginalName()) ?: 'image';

        $asset = MediaAsset::create([
            'user_id' => $user->id,
            'project_id' => $project?->id,
            'disk' => $disk,
            'path' => (string) $path,
            'original_name' => mb_substr($sanitizedOriginal, 0, 200),
            'mime_type' => $mimeType,
            'size_bytes' => (int) $file->getSize(),
            'width' => $width ?: null,
            'height' => $height ?: null,
        ]);

        $this->activityLog->log(
            user: $user,
            action: 'media.uploaded',
            description: "Uploaded image \"{$asset->original_name}\"",
            project: $project
        );

        return ApiResponse::success(
            new MediaAssetResource($asset),
            'Image uploaded successfully.',
            201
        );
    }

    public function destroy(Request $request, MediaAsset $mediaAsset): JsonResponse
    {
        $this->authorize('delete', $mediaAsset);

        Storage::disk($mediaAsset->disk)->delete($mediaAsset->path);
        $mediaAsset->delete();

        return ApiResponse::success(null, 'Media asset deleted successfully.');
    }
}
