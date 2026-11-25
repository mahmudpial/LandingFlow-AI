<script setup lang="ts">
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import BaseBadge from '@/components/ui/BaseBadge.vue';
import { Shield, Sparkles, Server, Database, CheckCircle2 } from 'lucide-vue-next';
</script>

<template>
  <DashboardLayout>
    <div class="mx-auto max-w-4xl space-y-6">
      <div class="rounded-2xl border border-brand-border bg-white p-6 shadow-card">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-bold text-brand-text">
            LandingFlow AI — Platform Guide & Architecture
          </h2>
          <BaseBadge variant="ai">Production SaaS MVP</BaseBadge>
        </div>
        <p class="mt-2 text-sm leading-relaxed text-brand-muted">
          LandingFlow AI is architected with a clean separation between the Vue 3 + TypeScript SPA,
          Laravel 13 REST API (`/api/v1`), provider-abstracted AI engine (`MockAIProvider` &
          `BedrockProvider`), and schema-driven landing page renderer.
        </p>

        <div class="mt-6 grid gap-4 sm:grid-cols-3">
          <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
            <div class="flex items-center gap-2 text-sm font-bold text-brand-text">
              <Sparkles class="h-4 w-4 text-brand-primary" />
              <span>AI Provider Abstraction</span>
            </div>
            <p class="mt-1.5 text-xs leading-relaxed text-slate-600">
              Uses `AI_PROVIDER=mock` locally at $0 cost and switches seamlessly to Amazon Bedrock
              Converse API (`AI_PROVIDER=bedrock`) in production.
            </p>
          </div>

          <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
            <div class="flex items-center gap-2 text-sm font-bold text-brand-text">
              <Shield class="h-4 w-4 text-brand-accent" />
              <span>OWASP & Sanctum Security</span>
            </div>
            <p class="mt-1.5 text-xs leading-relaxed text-slate-600">
              HTTP-only cookie sessions, CSRF protection, Laravel Policies preventing IDOR, and
              zero arbitrary HTML/JS execution.
            </p>
          </div>

          <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
            <div class="flex items-center gap-2 text-sm font-bold text-brand-text">
              <Database class="h-4 w-4 text-brand-secondary" />
              <span>SQLite & PostgreSQL</span>
            </div>
            <p class="mt-1.5 text-xs leading-relaxed text-slate-600">
              Portable Eloquent migrations run on local SQLite during development/CI and PostgreSQL
              16+ on AWS Lightsail.
            </p>
          </div>
        </div>
      </div>

      <div class="rounded-2xl border border-brand-border bg-white p-6 shadow-card">
        <h3 class="flex items-center gap-2 text-base font-bold text-brand-text">
          <Server class="h-4 w-4 text-brand-primary" />
          <span>4-Step Workflow to Launch a Landing Page</span>
        </h3>
        <ul class="mt-4 space-y-3 text-sm text-slate-600">
          <li class="flex items-start gap-2.5">
            <CheckCircle2 class="mt-0.5 h-4 w-4 shrink-0 text-brand-success" />
            <span>
              <strong>1. Generate with AI:</strong> Open the <em>AI Generator</em> wizard, enter your
              product description, target audience, and conversion goal.
            </span>
          </li>
          <li class="flex items-start gap-2.5">
            <CheckCircle2 class="mt-0.5 h-4 w-4 shrink-0 text-brand-success" />
            <span>
              <strong>2. Switch Templates Non-Destructively:</strong> Test <em>Startup SaaS</em>,
              <em>Product Launch</em>, or <em>Agency / Service</em> without losing your copy.
            </span>
          </li>
          <li class="flex items-start gap-2.5">
            <CheckCircle2 class="mt-0.5 h-4 w-4 shrink-0 text-brand-success" />
            <span>
              <strong>3. Customize & Upload Media:</strong> Reorder sections, edit copy, replace
              illustrative testimonials/pricing with verified details, and preview across Desktop,
              Tablet, and Mobile.
            </span>
          </li>
          <li class="flex items-start gap-2.5">
            <CheckCircle2 class="mt-0.5 h-4 w-4 shrink-0 text-brand-success" />
            <span>
              <strong>4. Publish Live:</strong> Click <em>Publish</em> in the editor toolbar to make
              your page live at <code>/p/your-unique-slug</code>.
            </span>
          </li>
        </ul>
      </div>
    </div>
  </DashboardLayout>
</template>
