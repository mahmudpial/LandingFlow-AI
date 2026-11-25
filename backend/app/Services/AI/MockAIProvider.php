<?php

declare(strict_types=1);

namespace App\Services\AI;

class MockAIProvider implements AIProviderInterface
{
    public function __construct(
        private readonly LandingPageSchemaValidator $validator,
    ) {}

    public function getProviderName(): string
    {
        return 'mock';
    }

    public function getModelId(): string
    {
        return 'landingflow-mock-v1';
    }

    public function generateLandingPage(array $input): AIProviderResult
    {
        $startTime = hrtime(true);

        $productName = trim((string) ($input['product_name'] ?? 'Acme Flow'));
        $description = trim((string) ($input['product_description'] ?? 'Modern AI workflow automation platform.'));
        $industry = trim((string) ($input['industry'] ?? 'SaaS & Technology'));
        $audience = trim((string) ($input['target_audience'] ?? 'Founders, operators, and high-velocity teams'));
        $goal = trim((string) ($input['website_goal'] ?? 'Generate qualified signups'));
        $tone = trim((string) ($input['brand_tone'] ?? 'Modern & authoritative'));
        $primaryCta = trim((string) ($input['primary_cta'] ?? 'Start Free Trial'));
        $keywords = is_array($input['keywords'] ?? null) ? array_values(array_filter(array_map('strval', $input['keywords']))) : [];

        if ($keywords === []) {
            $keywords = [$productName, $industry, 'automation', 'productivity'];
        }

        $primaryKeyword = $keywords[0] ?? $industry;

        $rawSchema = [
            'seo' => [
                'title' => "{$productName} — {$industry} Built for {$audience}",
                'description' => mb_substr("{$productName}: {$description} Purpose-built for {$audience} to {$goal}.", 0, 300),
                'keywords' => $keywords,
            ],
            'sections_order' => LandingPageSchemaValidator::SUPPORTED_SECTIONS,
            'sections' => [
                'hero' => [
                    'visible' => true,
                    'badge' => "{$industry} • {$tone}",
                    'headline' => "{$productName}: The Smarter Way for {$audience} to {$goal}",
                    'subheadline' => "{$description} Stop wrestling with disconnected tools and give your team a streamlined, high-converting workflow.",
                    'primary_cta_text' => $primaryCta,
                    'primary_cta_url' => '#pricing',
                    'secondary_cta_text' => 'See How It Works',
                    'secondary_cta_url' => '#features',
                    'image_url' => '',
                    'highlights' => [
                        'Fast 5-minute onboarding',
                        "Tailored for {$audience}",
                        'No complex coding required',
                    ],
                ],
                'problem_solution' => [
                    'visible' => true,
                    'title' => "Why {$audience} Are Moving Beyond Legacy {$industry} Tools",
                    'subtitle' => "Traditional approaches slow down execution. {$productName} replaces manual busywork with intelligent automation.",
                    'problem_title' => 'Without ' . $productName,
                    'problem_items' => [
                        'Hours lost stitching together spreadsheets and disconnected tools',
                        'Inconsistent execution and slow turnaround times across projects',
                        "Lack of clear visibility into what actually drives {$goal}",
                    ],
                    'solution_title' => 'With ' . $productName,
                    'solution_items' => [
                        "Centralized workspace purpose-built for {$industry}",
                        'Automated workflows that standardize quality from day one',
                        'Actionable insights so your team can focus on high-impact growth',
                    ],
                ],
                'features' => [
                    'visible' => true,
                    'title' => "Everything You Need to Excel in {$industry}",
                    'subtitle' => "{$productName} combines speed, clarity, and reliability in a single cohesive platform.",
                    'items' => [
                        [
                            'icon' => 'Sparkles',
                            'title' => "Intelligent {$primaryKeyword} Engine",
                            'description' => "Automate repetitive steps in your {$industry} workflow with guided templates and smart defaults.",
                        ],
                        [
                            'icon' => 'Zap',
                            'title' => 'Instant Real-Time Collaboration',
                            'description' => "Keep {$audience} aligned with live previews, structured feedback, and role-aware controls.",
                        ],
                        [
                            'icon' => 'Shield',
                            'title' => 'Enterprise-Grade Reliability & Security',
                            'description' => 'Built with strict access policies, validated schemas, and dependable cloud infrastructure.',
                        ],
                        [
                            'icon' => 'BarChart3',
                            'title' => 'Conversion-Focused Architecture',
                            'description' => "Every component is structured to guide visitors smoothly toward your primary goal: {$goal}.",
                        ],
                        [
                            'icon' => 'Layers',
                            'title' => 'Modular & Customizable',
                            'description' => 'Adapt layouts, palettes, and sections anytime without rebuilding from scratch.',
                        ],
                        [
                            'icon' => 'Globe',
                            'title' => 'One-Click Publishing',
                            'description' => 'Go live immediately with clean URLs, responsive layouts, and built-in SEO metadata.',
                        ],
                    ],
                ],
                'benefits' => [
                    'visible' => true,
                    'title' => "Built to Deliver Tangible Value for {$audience}",
                    'subtitle' => 'Focus on your core product and customers while we handle the heavy lifting.',
                    'items' => [
                        [
                            'title' => 'Launch in Minutes, Not Weeks',
                            'description' => 'Skip the blank-page syndrome and move directly from concept to a polished deliverable.',
                            'metric_label' => 'Faster Time-to-Launch',
                        ],
                        [
                            'title' => 'Consistent Brand Presentation',
                            'description' => "Maintain a {$tone} voice across every section with unified design tokens and typography.",
                            'metric_label' => 'Unified Brand Identity',
                        ],
                        [
                            'title' => 'Scale Without Bloated Overhead',
                            'description' => 'Run lean operations with predictable workflows designed for sustainable growth.',
                            'metric_label' => 'Lean Operations',
                        ],
                    ],
                ],
                'social_proof' => [
                    'visible' => true,
                    'is_illustrative' => true,
                    'disclaimer' => LandingPageSchemaValidator::ILLUSTRATIVE_DISCLAIMER,
                    'title' => "Trusted by Forward-Thinking {$industry} Teams (Illustrative Placeholder Logos)",
                    'logos' => [
                        'NorthStar Labs (Sample)',
                        'Vanguard Flow (Sample)',
                        'Apex Digital (Sample)',
                        'Meridian Cloud (Sample)',
                        'Summit Ops (Sample)',
                    ],
                ],
                'testimonials' => [
                    'visible' => true,
                    'is_illustrative' => true,
                    'disclaimer' => LandingPageSchemaValidator::ILLUSTRATIVE_DISCLAIMER,
                    'title' => 'Illustrative Customer Testimonials (Replace Before Publishing)',
                    'subtitle' => 'These sample cards demonstrate how customer quotes will appear on your page.',
                    'items' => [
                        [
                            'quote' => "[Sample Quote] Switching to {$productName} helped our team streamline our daily {$industry} workflow and focus on delivering value.",
                            'author_name' => 'Jordan Taylor (Illustrative Persona)',
                            'author_role' => 'Founder & Product Lead',
                            'company' => 'Sample Venture Co.',
                            'is_illustrative' => true,
                        ],
                        [
                            'quote' => "[Sample Quote] The clarity and speed of {$productName} made it easy for our stakeholders to align and launch ahead of schedule.",
                            'author_name' => 'Morgan Lee (Illustrative Persona)',
                            'author_role' => 'Director of Growth',
                            'company' => 'Example Studio',
                            'is_illustrative' => true,
                        ],
                        [
                            'quote' => '[Sample Quote] Simple to configure, clean user experience, and built around the exact workflows our team needed.',
                            'author_name' => 'Casey Chen (Illustrative Persona)',
                            'author_role' => 'Operations Manager',
                            'company' => 'Demo Systems Inc.',
                            'is_illustrative' => true,
                        ],
                    ],
                ],
                'pricing' => [
                    'visible' => true,
                    'is_illustrative' => true,
                    'disclaimer' => LandingPageSchemaValidator::ILLUSTRATIVE_DISCLAIMER,
                    'title' => 'Illustrative Pricing Suggestions',
                    'subtitle' => 'Suggested tier structure generated for your business model — edit prices and features to reflect your real offer.',
                    'plans' => [
                        [
                            'name' => 'Starter',
                            'price' => '$19',
                            'period' => '/month (Sample)',
                            'description' => 'For solo builders and early validation.',
                            'features' => [
                                'Core workspace & essential workflows',
                                'Up to 3 active projects',
                                'Standard email support',
                            ],
                            'cta_text' => $primaryCta,
                            'cta_url' => '#cta',
                            'is_popular' => false,
                        ],
                        [
                            'name' => 'Growth Pro',
                            'price' => '$49',
                            'period' => '/month (Sample)',
                            'description' => "Best for growing {$audience}.",
                            'features' => [
                                "Full {$productName} automation suite",
                                'Unlimited active projects & templates',
                                'Priority support & team collaboration',
                                'Custom branding & SEO controls',
                            ],
                            'cta_text' => $primaryCta,
                            'cta_url' => '#cta',
                            'is_popular' => true,
                        ],
                        [
                            'name' => 'Scale / Agency',
                            'price' => '$129',
                            'period' => '/month (Sample)',
                            'description' => 'For high-volume teams and agencies.',
                            'features' => [
                                'Everything in Growth Pro',
                                'Dedicated onboarding & SLA',
                                'Multi-client workspace management',
                            ],
                            'cta_text' => 'Contact Sales',
                            'cta_url' => '#cta',
                            'is_popular' => false,
                        ],
                    ],
                ],
                'faq' => [
                    'visible' => true,
                    'title' => 'Frequently Asked Questions',
                    'subtitle' => "Everything you need to know about getting started with {$productName}.",
                    'items' => [
                        [
                            'question' => "Who is {$productName} designed for?",
                            'answer' => "{$productName} is purpose-built for {$audience} in the {$industry} space looking to {$goal}.",
                        ],
                        [
                            'question' => 'How long does setup take?',
                            'answer' => 'Most users configure their workspace and launch their first workflow within 10 to 15 minutes.',
                        ],
                        [
                            'question' => 'Can I customize the setup as my business evolves?',
                            'answer' => 'Yes. Every section, workflow, and configuration can be updated at any time without losing your existing data.',
                        ],
                        [
                            'question' => 'Is my data kept private and secure?',
                            'answer' => 'Yes. We enforce strict access controls, encrypted sessions, and industry-standard data protection practices.',
                        ],
                    ],
                ],
                'cta' => [
                    'visible' => true,
                    'headline' => "Ready to Achieve Your Goal with {$productName}?",
                    'subheadline' => "Join {$audience} using {$productName} to simplify {$industry} and {$goal}.",
                    'button_text' => $primaryCta,
                    'button_url' => '#pricing',
                ],
                'footer' => [
                    'visible' => true,
                    'brand_name' => $productName,
                    'tagline' => $description,
                    'copyright_text' => sprintf('© %s %s. All rights reserved.', date('Y'), $productName),
                    'links' => [
                        ['label' => 'Features', 'url' => '#features'],
                        ['label' => 'Benefits', 'url' => '#benefits'],
                        ['label' => 'Pricing', 'url' => '#pricing'],
                        ['label' => 'FAQ', 'url' => '#faq'],
                    ],
                ],
            ],
        ];

        $validated = $this->validator->validateAndNormalize($rawSchema, $productName);
        $durationMs = (int) round((hrtime(true) - $startTime) / 1_000_000);

        return new AIProviderResult(
            provider: $this->getProviderName(),
            modelId: $this->getModelId(),
            content: $validated,
            promptTokens: 320,
            completionTokens: 890,
            durationMs: max(1, $durationMs),
        );
    }
}
