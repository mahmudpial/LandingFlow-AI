<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TemplateResource;
use App\Services\Templates\TemplateService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class TemplateController extends Controller
{
    public function __construct(
        private readonly TemplateService $templateService,
    ) {}

    public function index(): JsonResponse
    {
        $templates = $this->templateService->listActiveTemplates();

        return ApiResponse::success(
            TemplateResource::collection($templates),
            'Templates retrieved successfully.'
        );
    }
}
