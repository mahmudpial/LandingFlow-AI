<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Publishing\PublishingService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class PublicPageController extends Controller
{
    public function __construct(
        private readonly PublishingService $publishingService,
    ) {}

    public function show(string $slug): JsonResponse
    {
        $publishedPage = $this->publishingService->getPublicPageBySlug($slug);

        if (! $publishedPage) {
            return ApiResponse::error('Published page not found.', 404);
        }

        return ApiResponse::success([
            'slug' => $publishedPage->slug,
            'seo_title' => $publishedPage->seo_title,
            'seo_description' => $publishedPage->seo_description,
            'view_count' => $publishedPage->view_count,
            'published_at' => $publishedPage->published_at?->toIso8601String(),
            'snapshot' => $publishedPage->published_snapshot,
        ], 'Public page retrieved successfully.');
    }
}
