<?php

declare(strict_types=1);

namespace App\Enums;

enum TemplateCategory: string
{
    case SaaS = 'saas';
    case Product = 'product';
    case Agency = 'agency';
}
