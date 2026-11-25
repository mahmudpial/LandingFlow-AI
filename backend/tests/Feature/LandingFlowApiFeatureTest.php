<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Template;
use App\Models\User;
use Database\Seeders\TemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LandingFlowApiFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TemplateSeeder::class);
    }

    public function test_user_can_register_login_fetch_profile_update_account_and_logout(): void
    {
        // 1. Register
        $registerResponse = $this->postJson('/api/v1/auth/register', [
            'name' => 'Elena Vance',
            'email' => 'elena@landingflow.ai',
            'password' => 'SecurePass123',
            'password_confirmation' => 'SecurePass123',
            'company_name' => 'Vance Labs',
            'job_title' => 'Founder',
        ]);

        $registerResponse->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', 'elena@landingflow.ai')
            ->assertJsonPath('data.user.company_name', 'Vance Labs');

        // 2. Fetch current user (/me)
        $meResponse = $this->getJson('/api/v1/auth/me');
        $meResponse->assertOk()
            ->assertJsonPath('data.user.name', 'Elena Vance');

        // 3. Update profile
        $profileResponse = $this->putJson('/api/v1/auth/profile', [
            'name' => 'Elena Vance-updated',
            'email' => 'elena@landingflow.ai',
            'company_name' => 'Vance AI Studio',
            'job_title' => 'CEO',
        ]);
        $profileResponse->assertOk()
            ->assertJsonPath('data.user.name', 'Elena Vance-updated')
            ->assertJsonPath('data.user.company_name', 'Vance AI Studio');

        // 4. Verify Email
        $verifyResponse = $this->postJson('/api/v1/auth/email/verify');
        $verifyResponse->assertOk()
            ->assertJsonPath('data.user.email_verified', true);

        // 5. Logout
        $logoutResponse = $this->postJson('/api/v1/auth/logout');
        $logoutResponse->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_password_reset_flow_works_and_rejects_invalid_tokens(): void
    {
        User::factory()->create([
            'email' => 'reset@landingflow.ai',
        ]);

        $forgotResponse = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'reset@landingflow.ai',
        ]);

        $forgotResponse->assertOk();
        $token = $forgotResponse->json('data.debug_reset_token');
        $this->assertNotEmpty($token);

        // Reject wrong token
        $badReset = $this->postJson('/api/v1/auth/reset-password', [
            'email' => 'reset@landingflow.ai',
            'token' => 'wrong-token',
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ]);
        $badReset->assertStatus(422);

        // Accept valid token
        $goodReset = $this->postJson('/api/v1/auth/reset-password', [
            'email' => 'reset@landingflow.ai',
            'token' => $token,
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ]);
        $goodReset->assertOk();
    }

    public function test_project_crud_duplication_filtering_and_dashboard_stats(): void
    {
        $user = User::factory()->create();
        $template = Template::firstOrFail();

        $this->actingAs($user);

        // Create project
        $createResponse = $this->postJson('/api/v1/projects', [
            'name' => 'PulseMetrics Cloud',
            'description' => 'Automated SaaS analytics platform.',
            'template_id' => $template->id,
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.name', 'PulseMetrics Cloud')
            ->assertJsonPath('data.slug', 'pulsemetrics-cloud')
            ->assertJsonPath('data.status', 'draft');

        $projectId = $createResponse->json('data.id');

        // Duplicate project
        $dupResponse = $this->postJson("/api/v1/projects/{$projectId}/duplicate");
        $dupResponse->assertCreated()
            ->assertJsonPath('data.name', 'PulseMetrics Cloud (Copy)');

        // Search and filter
        $listResponse = $this->getJson('/api/v1/projects?search=PulseMetrics&status=draft');
        $listResponse->assertOk()
            ->assertJsonPath('meta.total', 2);

        // Dashboard reflects real counts
        $dashboardResponse = $this->getJson('/api/v1/dashboard');
        $dashboardResponse->assertOk()
            ->assertJsonPath('data.stats.total_projects', 2)
            ->assertJsonPath('data.stats.draft_projects', 2)
            ->assertJsonPath('data.stats.published_pages', 0);
    }

    public function test_strict_idor_authorization_prevents_other_users_from_accessing_projects(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $template = Template::firstOrFail();

        $this->actingAs($owner);
        $projectResp = $this->postJson('/api/v1/projects', [
            'name' => 'Owner Secret Project',
            'template_id' => $template->id,
        ]);
        $projectId = $projectResp->json('data.id');

        // Switch to intruder
        $this->actingAs($intruder);

        $this->getJson("/api/v1/projects/{$projectId}")->assertForbidden();
        $this->putJson("/api/v1/projects/{$projectId}", ['name' => 'Hacked'])->assertForbidden();
        $this->putJson("/api/v1/projects/{$projectId}/content", ['seo_title' => 'Hacked'])->assertForbidden();
        $this->postJson("/api/v1/projects/{$projectId}/duplicate")->assertForbidden();
        $this->postJson("/api/v1/projects/{$projectId}/publish", ['publish' => true])->assertForbidden();
        $this->deleteJson("/api/v1/projects/{$projectId}")->assertForbidden();
    }

    public function test_ai_generation_template_switching_editor_save_and_publishing_lifecycle(): void
    {
        $user = User::factory()->create();
        $templates = Template::all();
        $this->assertGreaterThanOrEqual(3, $templates->count());

        $this->actingAs($user);

        // 1. Create Project
        $createResp = $this->postJson('/api/v1/projects', [
            'name' => 'Vertex AI Copilot',
            'template_id' => $templates[0]->id,
        ]);
        $projectId = $createResp->json('data.id');

        // 2. Run AI Landing Page Generator
        $genResp = $this->postJson("/api/v1/projects/{$projectId}/generate", [
            'product_name' => 'Vertex AI Copilot',
            'product_description' => 'Automated code review and architecture documentation for engineering teams.',
            'industry' => 'Developer Tools',
            'target_audience' => 'Software Engineers & Engineering Managers',
            'website_goal' => 'Book Product Demo',
            'brand_tone' => 'Modern & Technical',
            'language' => 'English',
            'primary_cta' => 'Start Free Trial',
            'keywords' => ['code review', 'AI developer tools'],
        ]);

        $genResp->assertOk()
            ->assertJsonPath('data.generation.status', 'completed')
            ->assertJsonPath('data.generation.provider', 'mock')
            ->assertJsonPath('data.project.content.sections.testimonials.is_illustrative', true);

        // 3. Switch Template without losing generated copy
        $generatedHeadline = $genResp->json('data.project.content.sections.hero.headline');
        $secondTemplate = $templates[1];

        $switchResp = $this->postJson("/api/v1/projects/{$projectId}/apply-template", [
            'template_id' => $secondTemplate->id,
            'replace_content' => false,
        ]);

        $switchResp->assertOk()
            ->assertJsonPath('data.template_id', $secondTemplate->id)
            ->assertJsonPath('data.theme.template_slug', $secondTemplate->slug)
            ->assertJsonPath('data.content.sections.hero.headline', $generatedHeadline);

        // 4. Customize Content in Editor
        $content = $switchResp->json('data.content');
        $content['sections']['hero']['headline'] = 'Custom Edited Headline for Vertex AI';

        $saveResp = $this->putJson("/api/v1/projects/{$projectId}/content", [
            'content' => $content,
            'theme' => [
                'colors' => [
                    'primary' => '#14B8A6',
                ],
            ],
            'seo_title' => 'Vertex AI Copilot — Official Launch',
        ]);

        $saveResp->assertOk()
            ->assertJsonPath('data.content.sections.hero.headline', 'Custom Edited Headline for Vertex AI')
            ->assertJsonPath('data.theme.colors.primary', '#14B8A6');

        // 5. Verify draft page is NOT publicly accessible prior to publishing
        $this->getJson('/api/v1/pages/vertex-ai-copilot')->assertNotFound();

        // 6. Publish page
        $publishResp = $this->postJson("/api/v1/projects/{$projectId}/publish", [
            'publish' => true,
            'slug' => 'vertex-ai-copilot',
        ]);

        $publishResp->assertOk()
            ->assertJsonPath('data.status', 'published')
            ->assertJsonPath('data.slug', 'vertex-ai-copilot');

        // 7. Public visitor can view published page
        $publicResp = $this->getJson('/api/v1/pages/vertex-ai-copilot');
        $publicResp->assertOk()
            ->assertJsonPath('data.slug', 'vertex-ai-copilot')
            ->assertJsonPath('data.snapshot.content.sections.hero.headline', 'Custom Edited Headline for Vertex AI');

        // 8. Unpublish page and confirm public access is immediately revoked
        $unpublishResp = $this->postJson("/api/v1/projects/{$projectId}/publish", [
            'publish' => false,
        ]);
        $unpublishResp->assertOk()
            ->assertJsonPath('data.status', 'draft');

        $this->getJson('/api/v1/pages/vertex-ai-copilot')->assertNotFound();
    }

    public function test_secure_media_upload_accepts_valid_images_and_rejects_executables(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $this->actingAs($user);

        // Valid PNG image succeeds
        $validImage = UploadedFile::fake()->image('hero-banner.png', 800, 600);
        $uploadResp = $this->postJson('/api/v1/media', [
            'file' => $validImage,
        ]);

        $uploadResp->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.mime_type', 'image/png');

        // Disguised PHP script fails validation with 422
        $maliciousFile = UploadedFile::fake()->create('shell.php', 10, 'application/x-php');
        $badResp = $this->postJson('/api/v1/media', [
            'file' => $maliciousFile,
        ]);
        $badResp->assertStatus(422);

        // SVG file fails validation (prevents embedded script XSS)
        $svgFile = UploadedFile::fake()->create('icon.svg', 10, 'image/svg+xml');
        $svgResp = $this->postJson('/api/v1/media', [
            'file' => $svgFile,
        ]);
        $svgResp->assertStatus(422);
    }
}
