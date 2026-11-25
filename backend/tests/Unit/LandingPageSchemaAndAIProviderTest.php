<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\AI\BedrockProvider;
use App\Services\AI\LandingPageSchemaValidator;
use App\Services\AI\MockAIProvider;
use Aws\BedrockRuntime\BedrockRuntimeClient;
use Aws\Result;
use InvalidArgumentException;
use Mockery;
use Tests\TestCase;

class LandingPageSchemaAndAIProviderTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_mock_ai_provider_generates_valid_complete_schema_with_illustrative_disclaimers(): void
    {
        $validator = new LandingPageSchemaValidator();
        $provider = new MockAIProvider($validator);

        $result = $provider->generateLandingPage([
            'product_name' => 'CloudPulse Analytics',
            'product_description' => 'Real-time cloud cost observability for engineering teams.',
            'industry' => 'DevOps & Cloud SaaS',
            'target_audience' => 'Platform Engineers & CTOs',
            'website_goal' => 'Start Free 14-Day Trial',
            'brand_tone' => 'Technical & Authoritative',
            'language' => 'English',
            'primary_cta' => 'Start Free Trial',
            'keywords' => ['cloud cost', 'finops', 'observability'],
        ]);

        $this->assertSame('mock', $result->provider);
        $this->assertSame('landingflow-mock-v1', $result->modelId);
        $this->assertArrayHasKey('seo', $result->content);
        $this->assertArrayHasKey('sections', $result->content);
        $this->assertArrayHasKey('sections_order', $result->content);

        // Enforce truthfulness rules: testimonials, pricing, and social_proof MUST be marked illustrative
        $sections = $result->content['sections'];
        $this->assertTrue($sections['testimonials']['is_illustrative']);
        $this->assertNotEmpty($sections['testimonials']['disclaimer']);
        $this->assertTrue($sections['pricing']['is_illustrative']);
        $this->assertNotEmpty($sections['pricing']['disclaimer']);
        $this->assertTrue($sections['social_proof']['is_illustrative']);
    }

    public function test_schema_validator_strips_html_tags_and_blocks_javascript_urls(): void
    {
        $validator = new LandingPageSchemaValidator();

        $maliciousInput = [
            'seo' => [
                'title' => '<script>alert("xss")</script>Clean Title',
                'description' => '<b>Safe</b> description',
            ],
            'sections' => [
                'hero' => [
                    'headline' => '<img src=x onerror=alert(1)>Welcome Headline',
                    'primary_cta_text' => 'Click Me',
                    'primary_cta_url' => 'javascript:alert(document.cookie)',
                    'secondary_cta_url' => 'data:text/html,<script>alert(1)</script>',
                ],
                'features' => [
                    'title' => 'Features',
                    'items' => [],
                ],
                'cta' => [
                    'headline' => 'CTA',
                    'button_url' => 'https://example.com/signup',
                ],
                'footer' => [
                    'brand_name' => 'Acme',
                ],
            ],
        ];

        $normalized = $validator->validateAndNormalize($maliciousInput, 'Acme');

        $this->assertSame('alert("xss")Clean Title', $normalized['seo']['title']);
        $this->assertSame('Welcome Headline', $normalized['sections']['hero']['headline']);
        $this->assertSame('#pricing', $normalized['sections']['hero']['primary_cta_url']);
        $this->assertSame('#features', $normalized['sections']['hero']['secondary_cta_url']);
        $this->assertSame('https://example.com/signup', $normalized['sections']['cta']['button_url']);
    }

    public function test_schema_validator_rejects_missing_required_sections(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $validator = new LandingPageSchemaValidator();
        $validator->validateAndNormalize([
            'sections' => [
                'hero' => ['headline' => 'Only Hero'],
            ],
        ]);
    }

    public function test_bedrock_provider_parses_converse_api_response_and_enforces_schema(): void
    {
        $validator = new LandingPageSchemaValidator();
        $mockJson = json_encode([
            'seo' => ['title' => 'Bedrock SaaS', 'description' => 'Generated via Bedrock Converse'],
            'sections' => [
                'hero' => ['headline' => 'Bedrock Generated Headline', 'primary_cta_text' => 'Launch'],
                'features' => ['title' => 'Key Features', 'items' => []],
                'cta' => ['headline' => 'Get Started'],
                'footer' => ['brand_name' => 'Bedrock SaaS'],
            ],
        ]);

        $mockClient = Mockery::mock(BedrockRuntimeClient::class);
        $mockClient->shouldReceive('converse')
            ->once()
            ->andReturn(new Result([
                'output' => [
                    'message' => [
                        'content' => [
                            ['text' => "```json\n{$mockJson}\n```"],
                        ],
                    ],
                ],
                'usage' => [
                    'inputTokens' => 250,
                    'outputTokens' => 600,
                ],
            ]));

        $provider = new BedrockProvider($validator, $mockClient);
        $result = $provider->generateLandingPage([
            'product_name' => 'Bedrock SaaS',
            'product_description' => 'Cloud AI tool',
            'industry' => 'AI',
            'target_audience' => 'Developers',
            'website_goal' => 'Signups',
            'brand_tone' => 'Modern',
            'language' => 'English',
            'primary_cta' => 'Launch',
        ]);

        $this->assertSame('bedrock', $result->provider);
        $this->assertSame(250, $result->promptTokens);
        $this->assertSame(600, $result->completionTokens);
        $this->assertSame('Bedrock Generated Headline', $result->content['sections']['hero']['headline']);
        $this->assertTrue($result->content['sections']['testimonials']['is_illustrative']);
    }
}
