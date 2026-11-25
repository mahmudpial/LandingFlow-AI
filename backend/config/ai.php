<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Active AI Provider
    |--------------------------------------------------------------------------
    |
    | Supported providers: "mock", "bedrock"
    | Always default to "mock" for zero-cost local development and CI tests.
    |
    */
    'provider' => env('AI_PROVIDER', 'mock'),

    /*
    |--------------------------------------------------------------------------
    | Per-User Hourly Generation Rate Limit
    |--------------------------------------------------------------------------
    */
    'rate_limit_per_hour' => (int) env('AI_RATE_LIMIT_PER_HOUR', 15),

    /*
    |--------------------------------------------------------------------------
    | Prompt & Output Limits
    |--------------------------------------------------------------------------
    */
    'max_prompt_chars' => (int) env('AI_MAX_PROMPT_CHARS', 2500),
    'max_output_tokens' => (int) env('AI_MAX_OUTPUT_TOKENS', 4096),
    'temperature' => (float) env('AI_TEMPERATURE', 0.4),

    /*
    |--------------------------------------------------------------------------
    | Amazon Bedrock Configuration
    |--------------------------------------------------------------------------
    */
    'bedrock' => [
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        'model_id' => env('BEDROCK_MODEL_ID', 'anthropic.claude-3-5-sonnet-20241022-v2:0'),
        'timeout_seconds' => (int) env('BEDROCK_TIMEOUT_SECONDS', 45),
        'max_retries' => (int) env('BEDROCK_MAX_RETRIES', 2),
    ],
];
