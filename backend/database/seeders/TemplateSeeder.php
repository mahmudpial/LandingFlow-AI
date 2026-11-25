<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Template;
use App\Services\AI\LandingPageSchemaValidator;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        $validator = app(LandingPageSchemaValidator::class);

        $templates = [
            [
                'slug' => 'startup-saas',
                'name' => 'Linear Obsidian SaaS',
                'description' => 'High-contrast architectural layout crafted for B2B software platforms, engineering tools, and cloud infrastructure.',
                'category' => 'saas',
                'preview_image_url' => '/templates/startup-saas.svg',
                'color_palette' => [
                    'primary' => '#111318',
                    'secondary' => '#D94E28',
                    'accent' => '#15803D',
                    'background' => '#F7F6F2',
                    'surface' => '#FFFFFF',
                    'text' => '#111318',
                ],
                'typography' => [
                    'heading_font' => 'Plus Jakarta Sans',
                    'body_font' => 'Plus Jakarta Sans',
                    'scale' => 'editorial',
                ],
                'supported_sections' => LandingPageSchemaValidator::SUPPORTED_SECTIONS,
                'default_schema' => $validator->validateAndNormalize(
                    $this->buildStarterSchema(
                        'CloudPulse SaaS',
                        'B2B Cloud Infrastructure',
                        'Ship Production Workflows 3x Faster With Unified Cloud Automation',
                        'Built for engineering and product teams that demand clarity, speed, and enterprise reliability.',
                        'Start Free Trial'
                    ),
                    'CloudPulse SaaS'
                ),
                'is_active' => true,
            ],
            [
                'slug' => 'product-launch',
                'name' => 'Editorial Vermilion Launch',
                'description' => 'Bold, magazine-grade product showcase designed for flagship releases, desktop apps, and creator tools.',
                'category' => 'product',
                'preview_image_url' => '/templates/product-launch.svg',
                'color_palette' => [
                    'primary' => '#D94E28',
                    'secondary' => '#111318',
                    'accent' => '#0F766E',
                    'background' => '#FAF9F5',
                    'surface' => '#FFFFFF',
                    'text' => '#111318',
                ],
                'typography' => [
                    'heading_font' => 'Plus Jakarta Sans',
                    'body_font' => 'Plus Jakarta Sans',
                    'scale' => 'bold',
                ],
                'supported_sections' => LandingPageSchemaValidator::SUPPORTED_SECTIONS,
                'default_schema' => $validator->validateAndNormalize(
                    $this->buildStarterSchema(
                        'NovaLaunch Studio',
                        'Release 2.0 • Flagship Edition',
                        'Meet the Next Generation Command Surface for Product Builders',
                        'Everything you need to plan, design, and ship your flagship release from one cohesive studio.',
                        'Get Early Access'
                    ),
                    'NovaLaunch Studio'
                ),
                'is_active' => true,
            ],
            [
                'slug' => 'agency-service',
                'name' => 'Meridian Advisory & Studio',
                'description' => 'Refined, trust-anchored editorial system tailored for product consultancies, design studios, and advisory firms.',
                'category' => 'agency',
                'preview_image_url' => '/templates/agency-service.svg',
                'color_palette' => [
                    'primary' => '#14532D',
                    'secondary' => '#111318',
                    'accent' => '#B45309',
                    'background' => '#F7F6F2',
                    'surface' => '#FFFFFF',
                    'text' => '#111318',
                ],
                'typography' => [
                    'heading_font' => 'Plus Jakarta Sans',
                    'body_font' => 'Plus Jakarta Sans',
                    'scale' => 'editorial',
                ],
                'supported_sections' => LandingPageSchemaValidator::SUPPORTED_SECTIONS,
                'default_schema' => $validator->validateAndNormalize(
                    $this->buildStarterSchema(
                        'Meridian Digital Partners',
                        'Product & Engineering Advisory',
                        'We Design and Engineer Category-Defining Digital Products',
                        'Partner with a senior product, design, and engineering practice focused on measurable commercial outcomes.',
                        'Book Executive Briefing'
                    ),
                    'Meridian Digital Partners'
                ),
                'is_active' => true,
            ],
        ];

        foreach ($templates as $templateData) {
            Template::updateOrCreate(
                ['slug' => $templateData['slug']],
                $templateData
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function buildStarterSchema(
        string $brand,
        string $badge,
        string $headline,
        string $subheadline,
        string $primaryCta
    ): array {
        return [
            'seo' => [
                'title' => "{$brand} — {$badge}",
                'description' => $subheadline,
                'keywords' => [$brand, 'SaaS', 'Landing Page', 'Automation'],
            ],
            'sections_order' => LandingPageSchemaValidator::SUPPORTED_SECTIONS,
            'sections' => [
                'hero' => [
                    'visible' => true,
                    'badge' => $badge,
                    'headline' => $headline,
                    'subheadline' => $subheadline,
                    'primary_cta_text' => $primaryCta,
                    'primary_cta_url' => '#pricing',
                    'secondary_cta_text' => 'Explore Capabilities',
                    'secondary_cta_url' => '#features',
                    'image_url' => '',
                    'highlights' => ['Responsive by Default', 'Schema-Validated Copy', 'Instant Public URL'],
                ],
                'problem_solution' => [
                    'visible' => true,
                    'title' => 'Replace Fragmented Execution With a Cohesive System',
                    'subtitle' => 'Compare the friction of manual processes against a purpose-built platform.',
                    'problem_title' => 'Fragmented Legacy Setup',
                    'problem_items' => [
                        'Disconnected spreadsheets and scattered communication',
                        'Slow iteration cycles that delay customer feedback',
                        'Inconsistent branding across marketing touchpoints',
                    ],
                    'solution_title' => "Unified With {$brand}",
                    'solution_items' => [
                        'Single source of truth for your entire workflow',
                        'Rapid deployment with pre-validated building blocks',
                        'Polished, accessible experience on every device',
                    ],
                ],
                'features' => [
                    'visible' => true,
                    'title' => 'Engineered for Speed, Clarity, and Conversion',
                    'subtitle' => 'Every section is modular, customizable, and built on a clean design system.',
                    'items' => [
                        ['icon' => '01', 'title' => 'Structured Messaging', 'description' => 'Craft persuasive product positioning tailored to your audience.'],
                        ['icon' => '02', 'title' => 'Modular Architecture', 'description' => 'Reorder, customize, or toggle sections without breaking layout integrity.'],
                        ['icon' => '03', 'title' => 'Built-In Security', 'description' => 'Protected by strict schema validation and safe rendering defaults.'],
                        ['icon' => '04', 'title' => 'Fast Performance', 'description' => 'Lightweight, responsive layouts optimized for desktop, tablet, and mobile.'],
                        ['icon' => '05', 'title' => 'Conversion Hierarchy', 'description' => 'Clear calls-to-action and visual contrast designed to guide visitors.'],
                        ['icon' => '06', 'title' => 'Instant Publishing', 'description' => 'Publish updates to your live shareable slug in a single click.'],
                    ],
                ],
                'benefits' => [
                    'visible' => true,
                    'title' => 'Outcomes That Move Your Business Forward',
                    'subtitle' => 'Built for teams that value execution speed and quality.',
                    'items' => [
                        ['title' => 'Accelerate Time-to-Market', 'description' => 'Go from initial idea to a live landing page in minutes.', 'metric_label' => 'Rapid Launch'],
                        ['title' => 'Maintain Total Control', 'description' => 'Fine-tune every headline, color token, and CTA in the live editor.', 'metric_label' => 'Full Customization'],
                        ['title' => 'Zero Infrastructure Hassle', 'description' => 'Hosted and served reliably with clean SEO metadata.', 'metric_label' => 'Managed Delivery'],
                    ],
                ],
                'social_proof' => [
                    'visible' => true,
                    'is_illustrative' => true,
                    'disclaimer' => LandingPageSchemaValidator::ILLUSTRATIVE_DISCLAIMER,
                    'title' => 'Illustrative Partner Logos (Sample Placeholders)',
                    'logos' => ['AcmeCloud (Sample)', 'VertexLabs (Sample)', 'PulseOps (Sample)', 'NorthStar (Sample)'],
                ],
                'testimonials' => [
                    'visible' => true,
                    'is_illustrative' => true,
                    'disclaimer' => LandingPageSchemaValidator::ILLUSTRATIVE_DISCLAIMER,
                    'title' => 'Illustrative Testimonials (Sample Layout Preview)',
                    'subtitle' => 'Replace these sample placeholders with verified quotes from your customers.',
                    'items' => [
                        [
                            'quote' => "[Sample Quote] {$brand} gave our team a clean, structured way to launch and iterate without design bottlenecks.",
                            'author_name' => 'Alex Morgan (Sample Persona)',
                            'author_role' => 'VP of Product',
                            'company' => 'Example SaaS Co.',
                            'is_illustrative' => true,
                        ],
                        [
                            'quote' => '[Sample Quote] The workflow is intuitive, fast, and produces a polished result right out of the box.',
                            'author_name' => 'Taylor Reed (Sample Persona)',
                            'author_role' => 'Managing Director',
                            'company' => 'Sample Studio',
                            'is_illustrative' => true,
                        ],
                    ],
                ],
                'pricing' => [
                    'visible' => true,
                    'is_illustrative' => true,
                    'disclaimer' => LandingPageSchemaValidator::ILLUSTRATIVE_DISCLAIMER,
                    'title' => 'Suggested Pricing Tiers (Illustrative)',
                    'subtitle' => 'Customize these sample plans to match your real pricing model.',
                    'plans' => [
                        [
                            'name' => 'Starter',
                            'price' => '$29',
                            'period' => '/month (Sample)',
                            'description' => 'Essential tools for individuals and early projects.',
                            'features' => ['Core features included', 'Up to 3 active projects', 'Email support'],
                            'cta_text' => $primaryCta,
                            'cta_url' => '#cta',
                            'is_popular' => false,
                        ],
                        [
                            'name' => 'Professional',
                            'price' => '$79',
                            'period' => '/month (Sample)',
                            'description' => 'Full capabilities for growing teams.',
                            'features' => ['Unlimited active projects', 'Priority support', 'Custom branding & themes', 'Advanced SEO controls'],
                            'cta_text' => $primaryCta,
                            'cta_url' => '#cta',
                            'is_popular' => true,
                        ],
                    ],
                ],
                'faq' => [
                    'visible' => true,
                    'title' => 'Frequently Asked Questions',
                    'subtitle' => 'Answers to common questions about our platform.',
                    'items' => [
                        ['question' => 'Can I customize every section on this page?', 'answer' => 'Yes. Use the LandingFlow studio editor to modify copy, reorder sections, toggle visibility, and adjust colors.'],
                        ['question' => 'Can I switch templates later without losing my copy?', 'answer' => 'Yes. Switching templates updates your color palette, typography, and layout variant while preserving your text content.'],
                    ],
                ],
                'cta' => [
                    'visible' => true,
                    'headline' => "Ready to Get Started With {$brand}?",
                    'subheadline' => 'Launch your next project today with a clean, conversion-ready foundation.',
                    'button_text' => $primaryCta,
                    'button_url' => '#pricing',
                ],
                'footer' => [
                    'visible' => true,
                    'brand_name' => $brand,
                    'tagline' => $subheadline,
                    'copyright_text' => sprintf('© %s %s. All rights reserved.', date('Y'), $brand),
                    'links' => [
                        ['label' => 'Features', 'url' => '#features'],
                        ['label' => 'Pricing', 'url' => '#pricing'],
                        ['label' => 'FAQ', 'url' => '#faq'],
                    ],
                ],
            ],
        ];
    }
}
