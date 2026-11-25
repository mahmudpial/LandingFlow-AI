<script setup lang="ts">
import { ref } from 'vue';
import BaseButton from '@/components/ui/BaseButton.vue';

const activePreviewTab = ref<'saas' | 'launch' | 'agency'>('saas');

const previewThemes = {
  saas: {
    label: '01 / B2B SaaS Velocity',
    slug: 'landingflow.studio/p/cloudpulse-finops',
    badge: 'CLOUD INFRASTRUCTURE',
    headline: 'Cut cloud waste by 38% without slowing engineering velocity.',
    sub: 'Automated unit-cost attribution, anomaly guardrails, and Slack-native approvals for scaling platform teams.',
    cta: 'Start 14-Day Free Trial',
    accent: '#111318',
    tagColor: '#D94E28',
  },
  launch: {
    label: '02 / Flagship Product Launch',
    slug: 'landingflow.studio/p/raycast-studio-2',
    badge: 'RELEASE 2.4 • PUBLIC BETA',
    headline: 'A faster command surface for product designers & engineers.',
    sub: 'Keyboard-first workflows, instant design token sync, and zero-latency previewing right from your desktop.',
    cta: 'Download Release 2.4',
    accent: '#D94E28',
    tagColor: '#15803D',
  },
  agency: {
    label: '03 / Advisory & Design Studio',
    slug: 'landingflow.studio/p/meridian-partners',
    badge: 'PRODUCT & REVENUE ADVISORY',
    headline: 'We partner with Series A founders to ship category-defining products.',
    sub: 'Senior engineering, conversion architecture, and design systems delivered in focused 6-week sprints.',
    cta: 'Book Executive Briefing',
    accent: '#14532D',
    tagColor: '#111318',
  },
};
</script>

<template>
  <div class="min-h-screen bg-brand-bg text-brand-text">
    <!-- Top Editorial Navigation -->
    <header class="sticky top-0 z-30 border-b border-brand-border bg-brand-bg/90 backdrop-blur-md">
      <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-3">
          <img src="/logo.svg" alt="LandingFlow" class="h-8 w-8 rounded-lg shadow-2xs" />
          <div class="flex items-baseline gap-2">
            <span class="text-base font-extrabold tracking-tight text-brand-text">LandingFlow</span>
            <span class="hidden rounded border border-brand-border bg-white px-1.5 py-0.5 font-mono text-[10px] font-semibold uppercase tracking-widest text-brand-muted sm:inline-block">
              Studio v1.0
            </span>
          </div>
        </div>

        <nav class="hidden items-center gap-8 text-[13px] font-medium text-brand-muted md:flex">
          <a href="#architecture" class="transition hover:text-brand-text">Architecture</a>
          <a href="#interactive-preview" class="transition hover:text-brand-text">Layout Systems</a>
          <a href="#workflow" class="transition hover:text-brand-text">Workflow</a>
        </nav>

        <div class="flex items-center gap-2.5">
          <RouterLink to="/login">
            <BaseButton variant="ghost" size="sm">Sign In</BaseButton>
          </RouterLink>
          <RouterLink to="/register">
            <BaseButton variant="primary" size="sm">Open Studio →</BaseButton>
          </RouterLink>
        </div>
      </div>
    </header>

    <!-- Hero Section with Subtle Architectural Grid -->
    <section class="bg-architectural-grid relative border-b border-brand-border px-4 pb-20 pt-16 sm:px-6 lg:pt-24">
      <div class="mx-auto max-w-5xl text-center animate-fade-up">
        <!-- Editorial Issue Pill -->
        <div class="inline-flex items-center gap-2.5 rounded-full border border-brand-border bg-white px-3.5 py-1.5 shadow-2xs">
          <span class="h-2 w-2 rounded-full bg-brand-secondary" />
          <span class="font-mono text-[11px] font-semibold uppercase tracking-[0.12em] text-brand-text">
            Conversion Page Architecture for Modern Software Teams
          </span>
        </div>

        <h1 class="mx-auto mt-8 max-w-4xl text-4xl font-extrabold leading-[1.08] tracking-tight text-brand-text sm:text-6xl lg:text-[64px]">
          Craft high-converting landing pages with
          <span class="font-serif italic font-normal text-brand-secondary">editorial clarity.</span>
        </h1>

        <p class="mx-auto mt-6 max-w-2xl text-base leading-relaxed text-brand-muted sm:text-lg">
          Turn a structured product brief into a 10-section conversion page. Customize typography,
          color tokens, and responsive viewports in a tactile live studio—then publish to a clean
          public slug in one click.
        </p>

        <div class="mt-9 flex flex-wrap items-center justify-center gap-3.5">
          <RouterLink to="/register">
            <BaseButton size="lg" variant="primary">
              <span>Start Building in Studio</span>
              <span aria-hidden="true">→</span>
            </BaseButton>
          </RouterLink>
          <RouterLink to="/login">
            <BaseButton size="lg" variant="outline">
              Sign In to Existing Workspace
            </BaseButton>
          </RouterLink>
        </div>

        <!-- Technical Spec Bar -->
        <div class="mx-auto mt-10 flex max-w-2xl flex-wrap items-center justify-center gap-x-8 gap-y-2 border-t border-brand-border pt-6 font-mono text-[11px] text-brand-muted">
          <span>01 / 10-SECTION VALIDATED SCHEMA</span>
          <span>02 / NON-DESTRUCTIVE LAYOUT SWITCHING</span>
          <span>03 / INSTANT PUBLIC SLUG (/p/slug)</span>
        </div>
      </div>

      <!-- Interactive Studio Window Mockup -->
      <div
        id="interactive-preview"
        class="mx-auto mt-14 max-w-6xl animate-fade-up stagger-2"
      >
        <!-- Preset Switcher Tabs -->
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
          <div class="flex flex-wrap gap-1.5">
            <button
              v-for="(item, key) in previewThemes"
              :key="key"
              type="button"
              class="rounded-lg border px-3.5 py-1.5 font-mono text-xs font-medium transition-all"
              :class="
                activePreviewTab === key
                  ? 'border-brand-primary bg-brand-primary text-white shadow-2xs'
                  : 'border-brand-border bg-white text-brand-muted hover:text-brand-text'
              "
              @click="activePreviewTab = key"
            >
              {{ item.label }}
            </button>
          </div>
          <span class="font-mono text-[11px] text-brand-muted">
            LIVE INTERACTIVE SCHEMA PREVIEW
          </span>
        </div>

        <!-- Browser Frame -->
        <div class="overflow-hidden rounded-2xl border border-brand-border bg-white shadow-elevated">
          <!-- Window Chrome Bar -->
          <div class="flex items-center justify-between border-b border-brand-border bg-[#F3F1EC] px-4 py-3">
            <div class="flex items-center gap-2">
              <span class="h-2.5 w-2.5 rounded-full bg-[#D6D3CD]" />
              <span class="h-2.5 w-2.5 rounded-full bg-[#D6D3CD]" />
              <span class="h-2.5 w-2.5 rounded-full bg-[#D6D3CD]" />
            </div>
            <div class="rounded-md border border-brand-border bg-white px-4 py-1 font-mono text-[11px] text-brand-muted">
              https://{{ previewThemes[activePreviewTab].slug }}
            </div>
            <div class="font-mono text-[10px] font-semibold uppercase tracking-widest text-brand-success">
              ● PUBLISHED
            </div>
          </div>

          <!-- Simulated Studio Split View -->
          <div class="grid lg:grid-cols-12">
            <!-- Left Inspector Preview (4 cols) -->
            <div class="hidden border-r border-brand-border bg-[#FAF9F6] p-6 lg:col-span-4 lg:block">
              <div class="flex items-center justify-between border-b border-brand-border pb-3">
                <span class="font-mono text-[10px] font-bold uppercase tracking-widest text-brand-muted">
                  SECTION ARCHITECTURE
                </span>
                <span class="font-mono text-[10px] text-brand-secondary">10 BLOCKS</span>
              </div>

              <div class="mt-4 space-y-2 font-mono text-xs">
                <div class="flex items-center justify-between rounded-md border border-brand-primary bg-white px-3 py-2 font-semibold text-brand-text shadow-2xs">
                  <span>01. Hero Statement</span>
                  <span class="text-[10px] text-brand-secondary">ACTIVE</span>
                </div>
                <div class="flex items-center justify-between rounded-md border border-brand-border bg-white/70 px-3 py-2 text-brand-muted">
                  <span>02. Problem vs. Solution</span>
                  <span class="text-[10px]">VISIBLE</span>
                </div>
                <div class="flex items-center justify-between rounded-md border border-brand-border bg-white/70 px-3 py-2 text-brand-muted">
                  <span>03. Core Capabilities Grid</span>
                  <span class="text-[10px]">VISIBLE</span>
                </div>
                <div class="flex items-center justify-between rounded-md border border-brand-border bg-white/70 px-3 py-2 text-brand-muted">
                  <span>04. Outcomes & Metrics</span>
                  <span class="text-[10px]">VISIBLE</span>
                </div>
                <div class="flex items-center justify-between rounded-md border border-brand-border bg-white/70 px-3 py-2 text-brand-muted">
                  <span>05. Pricing Architecture</span>
                  <span class="text-[10px]">VISIBLE</span>
                </div>
              </div>

              <div class="mt-6 rounded-lg border border-brand-border bg-white p-3.5">
                <p class="font-mono text-[10px] font-bold uppercase tracking-wider text-brand-muted">
                  TYPOGRAPHY & PALETTE
                </p>
                <div class="mt-2.5 flex items-center justify-between">
                  <span class="text-xs font-medium text-brand-text">Plus Jakarta + Instrument</span>
                  <div class="flex gap-1.5">
                    <span class="h-3.5 w-3.5 rounded-full bg-[#111318]" />
                    <span class="h-3.5 w-3.5 rounded-full bg-[#D94E28]" />
                    <span class="h-3.5 w-3.5 rounded-full bg-[#15803D]" />
                  </div>
                </div>
              </div>
            </div>

            <!-- Right Rendered Canvas Preview (8 cols) -->
            <div class="p-8 sm:p-12 lg:col-span-8">
              <div class="max-w-xl">
                <span
                  class="inline-block rounded px-2.5 py-1 font-mono text-[10px] font-bold uppercase tracking-widest text-white"
                  :style="{ backgroundColor: previewThemes[activePreviewTab].tagColor }"
                >
                  {{ previewThemes[activePreviewTab].badge }}
                </span>
                <h2 class="mt-5 text-2xl font-extrabold leading-tight tracking-tight text-brand-text sm:text-4xl">
                  {{ previewThemes[activePreviewTab].headline }}
                </h2>
                <p class="mt-4 text-sm leading-relaxed text-brand-muted sm:text-base">
                  {{ previewThemes[activePreviewTab].sub }}
                </p>
                <div class="mt-7 flex flex-wrap items-center gap-3">
                  <RouterLink to="/register">
                    <button
                      type="button"
                      class="rounded-lg px-5 py-2.5 text-xs font-semibold text-white shadow-tactile transition hover:opacity-95"
                      :style="{ backgroundColor: previewThemes[activePreviewTab].accent }"
                    >
                      {{ previewThemes[activePreviewTab].cta }} →
                    </button>
                  </RouterLink>
                  <span class="font-mono text-[11px] text-brand-muted">
                    Schema-verified • Responsive
                  </span>
                </div>
              </div>

              <!-- Mini Wireframe Cards Inside Preview -->
              <div class="mt-10 grid gap-3 border-t border-brand-border pt-6 sm:grid-cols-3">
                <div class="rounded-lg border border-brand-border bg-[#FAF9F6] p-3.5">
                  <span class="font-mono text-[10px] font-semibold text-brand-secondary">01</span>
                  <p class="mt-1 text-xs font-bold text-brand-text">Problem & Solution</p>
                  <p class="mt-0.5 text-[11px] text-brand-muted">Side-by-side contrast matrix</p>
                </div>
                <div class="rounded-lg border border-brand-border bg-[#FAF9F6] p-3.5">
                  <span class="font-mono text-[10px] font-semibold text-brand-secondary">02</span>
                  <p class="mt-1 text-xs font-bold text-brand-text">Transparent Proof</p>
                  <p class="mt-0.5 text-[11px] text-brand-muted">Honest illustrative guardrails</p>
                </div>
                <div class="rounded-lg border border-brand-border bg-[#FAF9F6] p-3.5">
                  <span class="font-mono text-[10px] font-semibold text-brand-secondary">03</span>
                  <p class="mt-1 text-xs font-bold text-brand-text">Conversion Tiers</p>
                  <p class="mt-0.5 text-[11px] text-brand-muted">Structured pricing & FAQ</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Bento Architecture Grid -->
    <section id="architecture" class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
      <div class="max-w-2xl">
        <span class="font-mono text-xs font-semibold uppercase tracking-[0.15em] text-brand-secondary">
          System Architecture
        </span>
        <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-brand-text sm:text-4xl">
          Engineered like a serious software product, not a toy template.
        </h2>
      </div>

      <div class="mt-12 grid gap-6 md:grid-cols-3">
        <div class="card-hover-lift rounded-xl border border-brand-border bg-white p-7 shadow-card">
          <div class="flex items-center justify-between border-b border-brand-border pb-4">
            <span class="font-mono text-xs font-bold text-brand-secondary">MODULE 01</span>
            <span class="font-mono text-[11px] text-brand-muted">SCHEMA ENGINE</span>
          </div>
          <h3 class="mt-5 text-lg font-bold text-brand-text">
            Structured Copy Architect
          </h3>
          <p class="mt-2.5 text-sm leading-relaxed text-brand-muted">
            Synthesizes your product brief, audience, and conversion goal into 10 distinct landing
            page sections with explicit illustrative disclaimers—never fabricating fake claims.
          </p>
        </div>

        <div class="card-hover-lift rounded-xl border border-brand-border bg-white p-7 shadow-card">
          <div class="flex items-center justify-between border-b border-brand-border pb-4">
            <span class="font-mono text-xs font-bold text-brand-secondary">MODULE 02</span>
            <span class="font-mono text-[11px] text-brand-muted">LIVE STUDIO</span>
          </div>
          <h3 class="mt-5 text-lg font-bold text-brand-text">
            Tactile Responsive Editor
          </h3>
          <p class="mt-2.5 text-sm leading-relaxed text-brand-muted">
            Reorder sections, toggle visibility, fine-tune color tokens and typography pairings,
            upload validated media assets, and preview across Desktop, Tablet, and Mobile with
            30-step Undo/Redo.
          </p>
        </div>

        <div class="card-hover-lift rounded-xl border border-brand-border bg-white p-7 shadow-card">
          <div class="flex items-center justify-between border-b border-brand-border pb-4">
            <span class="font-mono text-xs font-bold text-brand-secondary">MODULE 03</span>
            <span class="font-mono text-[11px] text-brand-muted">EDGE DELIVERY</span>
          </div>
          <h3 class="mt-5 text-lg font-bold text-brand-text">
            Immutable Snapshot Publishing
          </h3>
          <p class="mt-2.5 text-sm leading-relaxed text-brand-muted">
            Publish an immutable JSON snapshot to `/p/your-slug`. Keep iterating on drafts in the
            editor without breaking your live public conversion page until you republish.
          </p>
        </div>
      </div>
    </section>

    <!-- Editorial Bottom Banner -->
    <section id="workflow" class="border-t border-brand-border bg-[#111318] px-4 py-20 text-[#FAF9F5] sm:px-6">
      <div class="mx-auto flex max-w-5xl flex-col items-center justify-between gap-8 text-center sm:flex-row sm:text-left">
        <div class="max-w-xl">
          <span class="font-mono text-xs uppercase tracking-[0.16em] text-brand-secondary">
            Ready for Launch
          </span>
          <h2 class="mt-2 text-3xl font-bold tracking-tight text-white">
            Build your next product launch page today.
          </h2>
          <p class="mt-2 text-sm text-[#A1A09A]">
            Full access to the 3-step Copy Architect, 3 layout systems, and live responsive studio.
          </p>
        </div>
        <div class="flex shrink-0 gap-3">
          <RouterLink to="/register">
            <BaseButton size="lg" variant="secondary">
              Create Studio Account →
            </BaseButton>
          </RouterLink>
        </div>
      </div>
    </section>
  </div>
</template>
