<script setup lang="ts">
import { ref } from 'vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import ThemeToggle from '@/components/ui/ThemeToggle.vue';

const activePreviewTab = ref<'saas' | 'launch' | 'agency'>('saas');

const previewThemes = {
  saas: {
    label: '01 / B2B SaaS Velocity',
    slug: 'landingflow.studio/p/cloudpulse-finops',
    badge: 'CLOUD INFRASTRUCTURE',
    headline: 'Cut cloud waste by 38% without slowing engineering velocity.',
    sub: 'Automated unit-cost attribution, anomaly guardrails, and Slack-native approvals for scaling platform teams.',
    cta: 'Start 14-Day Free Trial',
    tagColor: '#E11D48',
  },
  launch: {
    label: '02 / Flagship Product Launch',
    slug: 'landingflow.studio/p/raycast-studio-2',
    badge: 'RELEASE 2.4 • PUBLIC BETA',
    headline: 'A faster command surface for product designers & engineers.',
    sub: 'Keyboard-first workflows, instant design token sync, and zero-latency previewing right from your desktop.',
    cta: 'Download Release 2.4',
    tagColor: '#059669',
  },
  agency: {
    label: '03 / Advisory & Design Studio',
    slug: 'landingflow.studio/p/meridian-partners',
    badge: 'PRODUCT & REVENUE ADVISORY',
    headline: 'We partner with Series A founders to ship category-defining products.',
    sub: 'Senior engineering, conversion architecture, and design systems delivered in focused 6-week sprints.',
    cta: 'Book Executive Briefing',
    tagColor: '#2563EB',
  },
};
</script>

<template>
  <div class="min-h-screen bg-[#FAFAFA] text-zinc-900 dark:bg-[#09090B] dark:text-zinc-100">
    <!-- Top Editorial Navigation -->
    <header class="sticky top-0 z-30 border-b border-zinc-200/80 bg-white/90 backdrop-blur-md dark:border-zinc-800 dark:bg-zinc-900/90">
      <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-2.5">
          <img src="/logo.svg" alt="LandingFlow" class="h-7 w-7 rounded-md shadow-sm" />
          <div class="flex items-baseline gap-2">
            <span class="text-[15px] font-bold tracking-tight text-zinc-900 dark:text-white">LandingFlow</span>
            <span class="hidden rounded border border-zinc-200 bg-zinc-50 px-1.5 py-0.5 font-mono text-[10px] font-medium uppercase tracking-widest text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 sm:inline-block">
              Studio v1.0
            </span>
          </div>
        </div>

        <nav class="hidden items-center gap-7 text-[13px] font-medium text-zinc-600 dark:text-zinc-300 md:flex">
          <a href="#architecture" class="transition hover:text-zinc-950 dark:hover:text-white">Architecture</a>
          <a href="#interactive-preview" class="transition hover:text-zinc-950 dark:hover:text-white">Layout Systems</a>
          <a href="#workflow" class="transition hover:text-zinc-950 dark:hover:text-white">Workflow</a>
        </nav>

        <div class="flex items-center gap-2">
          <ThemeToggle :show-label="true" />
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
    <section class="bg-architectural-grid relative border-b border-zinc-200 px-4 pb-20 pt-16 dark:border-zinc-800 sm:px-6 lg:pt-24">
      <div class="mx-auto max-w-5xl text-center animate-fade-up">
        <!-- Editorial Issue Pill -->
        <div class="inline-flex items-center gap-2.5 rounded-full border border-zinc-200 bg-white px-3.5 py-1.5 shadow-subtle dark:border-zinc-800 dark:bg-zinc-900">
          <span class="h-2 w-2 rounded-full bg-rose-600" />
          <span class="font-mono text-[11px] font-semibold uppercase tracking-[0.12em] text-zinc-800 dark:text-zinc-200">
            Conversion Page Architecture for Modern Software Teams
          </span>
        </div>

        <h1 class="mx-auto mt-8 max-w-4xl text-4xl font-extrabold leading-[1.08] tracking-tight text-zinc-950 dark:text-white sm:text-6xl lg:text-[62px]">
          Craft high-converting landing pages with
          <span class="font-serif italic font-normal text-rose-600 dark:text-rose-400">editorial clarity.</span>
        </h1>

        <p class="mx-auto mt-6 max-w-2xl text-base leading-relaxed text-zinc-600 dark:text-zinc-300 sm:text-lg">
          Turn a structured product brief into a 10-section conversion page. Customize typography,
          color tokens, and responsive viewports in a tactile live studio—then publish to a clean
          public slug in one click.
        </p>

        <div class="mt-9 flex flex-wrap items-center justify-center gap-3">
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
        <div class="mx-auto mt-10 flex max-w-2xl flex-wrap items-center justify-center gap-x-8 gap-y-2 border-t border-zinc-200 pt-6 font-mono text-[11px] text-zinc-500 dark:border-zinc-800 dark:text-zinc-400">
          <span>01 / 10-SECTION VALIDATED SCHEMA</span>
          <span>02 / NON-DESTRUCTIVE LAYOUT SWITCHING</span>
          <span>03 / INSTANT PUBLIC SLUG (/p/slug)</span>
        </div>
      </div>

      <!-- Interactive Studio Window Mockup -->
      <div
        id="interactive-preview"
        class="mx-auto mt-14 max-w-6xl animate-fade-up"
      >
        <!-- Preset Switcher Tabs -->
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
          <div class="flex flex-wrap gap-1.5">
            <button
              v-for="(item, key) in previewThemes"
              :key="key"
              type="button"
              class="rounded-md border px-3 py-1.5 font-mono text-xs font-medium transition-all"
              :class="
                activePreviewTab === key
                  ? 'border-zinc-900 bg-zinc-900 text-white shadow-sm dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-950'
                  : 'border-zinc-200 bg-white text-zinc-600 hover:text-zinc-900 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:text-white'
              "
              @click="activePreviewTab = key"
            >
              {{ item.label }}
            </button>
          </div>
          <span class="font-mono text-[11px] text-zinc-500 dark:text-zinc-400">
            LIVE INTERACTIVE SCHEMA PREVIEW
          </span>
        </div>

        <!-- Browser Frame -->
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-elevated dark:border-zinc-800 dark:bg-zinc-900">
          <!-- Window Chrome Bar -->
          <div class="flex items-center justify-between border-b border-zinc-200 bg-zinc-100/80 px-4 py-2.5 dark:border-zinc-800 dark:bg-zinc-950">
            <div class="flex items-center gap-2">
              <span class="h-2.5 w-2.5 rounded-full bg-zinc-300 dark:bg-zinc-700" />
              <span class="h-2.5 w-2.5 rounded-full bg-zinc-300 dark:bg-zinc-700" />
              <span class="h-2.5 w-2.5 rounded-full bg-zinc-300 dark:bg-zinc-700" />
            </div>
            <div class="rounded border border-zinc-200 bg-white px-4 py-1 font-mono text-[11px] text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300">
              https://{{ previewThemes[activePreviewTab].slug }}
            </div>
            <div class="font-mono text-[10px] font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400">
              ● PUBLISHED
            </div>
          </div>

          <!-- Simulated Studio Split View -->
          <div class="grid lg:grid-cols-12">
            <!-- Left Inspector Preview (4 cols) -->
            <div class="hidden border-r border-zinc-200 bg-zinc-50/70 p-6 dark:border-zinc-800 dark:bg-zinc-950/60 lg:col-span-4 lg:block">
              <div class="flex items-center justify-between border-b border-zinc-200 pb-3 dark:border-zinc-800">
                <span class="font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 dark:text-zinc-400">
                  SECTION ARCHITECTURE
                </span>
                <span class="font-mono text-[10px] text-rose-600 dark:text-rose-400">10 BLOCKS</span>
              </div>

              <div class="mt-4 space-y-2 font-mono text-xs">
                <div class="flex items-center justify-between rounded-md border border-zinc-900 bg-white px-3 py-2 font-semibold text-zinc-900 shadow-subtle dark:border-zinc-200 dark:bg-zinc-900 dark:text-white">
                  <span>01. Hero Statement</span>
                  <span class="text-[10px] text-rose-600 dark:text-rose-400">ACTIVE</span>
                </div>
                <div class="flex items-center justify-between rounded-md border border-zinc-200 bg-white/80 px-3 py-2 text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900/60 dark:text-zinc-300">
                  <span>02. Problem vs. Solution</span>
                  <span class="text-[10px] text-zinc-400">VISIBLE</span>
                </div>
                <div class="flex items-center justify-between rounded-md border border-zinc-200 bg-white/80 px-3 py-2 text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900/60 dark:text-zinc-300">
                  <span>03. Core Capabilities Grid</span>
                  <span class="text-[10px] text-zinc-400">VISIBLE</span>
                </div>
                <div class="flex items-center justify-between rounded-md border border-zinc-200 bg-white/80 px-3 py-2 text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900/60 dark:text-zinc-300">
                  <span>04. Outcomes & Metrics</span>
                  <span class="text-[10px] text-zinc-400">VISIBLE</span>
                </div>
                <div class="flex items-center justify-between rounded-md border border-zinc-200 bg-white/80 px-3 py-2 text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900/60 dark:text-zinc-300">
                  <span>05. Pricing Architecture</span>
                  <span class="text-[10px] text-zinc-400">VISIBLE</span>
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
                <h2 class="mt-5 text-2xl font-extrabold leading-tight tracking-tight text-zinc-950 dark:text-white sm:text-4xl">
                  {{ previewThemes[activePreviewTab].headline }}
                </h2>
                <p class="mt-4 text-sm leading-relaxed text-zinc-600 dark:text-zinc-300 sm:text-base">
                  {{ previewThemes[activePreviewTab].sub }}
                </p>
                <div class="mt-7 flex flex-wrap items-center gap-3">
                  <RouterLink to="/register">
                    <BaseButton variant="primary" size="lg">
                      {{ previewThemes[activePreviewTab].cta }} →
                    </BaseButton>
                  </RouterLink>
                  <span class="font-mono text-[11px] text-zinc-500 dark:text-zinc-400">
                    Schema-verified • Responsive
                  </span>
                </div>
              </div>

              <!-- Mini Wireframe Cards Inside Preview -->
              <div class="mt-10 grid gap-3 border-t border-zinc-200 pt-6 dark:border-zinc-800 sm:grid-cols-3">
                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3.5 dark:border-zinc-800 dark:bg-zinc-950">
                  <span class="font-mono text-[10px] font-semibold text-rose-600 dark:text-rose-400">01</span>
                  <p class="mt-1 text-xs font-bold text-zinc-900 dark:text-zinc-100">Problem & Solution</p>
                  <p class="mt-0.5 text-[11px] text-zinc-500 dark:text-zinc-400">Side-by-side contrast matrix</p>
                </div>
                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3.5 dark:border-zinc-800 dark:bg-zinc-950">
                  <span class="font-mono text-[10px] font-semibold text-rose-600 dark:text-rose-400">02</span>
                  <p class="mt-1 text-xs font-bold text-zinc-900 dark:text-zinc-100">Transparent Proof</p>
                  <p class="mt-0.5 text-[11px] text-zinc-500 dark:text-zinc-400">Honest illustrative guardrails</p>
                </div>
                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3.5 dark:border-zinc-800 dark:bg-zinc-950">
                  <span class="font-mono text-[10px] font-semibold text-rose-600 dark:text-rose-400">03</span>
                  <p class="mt-1 text-xs font-bold text-zinc-900 dark:text-zinc-100">Conversion Tiers</p>
                  <p class="mt-0.5 text-[11px] text-zinc-500 dark:text-zinc-400">Structured pricing & FAQ</p>
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
        <span class="font-mono text-xs font-semibold uppercase tracking-[0.15em] text-rose-600 dark:text-rose-400">
          System Architecture
        </span>
        <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-zinc-950 dark:text-white sm:text-4xl">
          Engineered like a serious software product, not a toy template.
        </h2>
      </div>

      <div class="mt-12 grid gap-6 md:grid-cols-3">
        <div class="rounded-xl border border-zinc-200 bg-white p-7 shadow-subtle transition hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
          <div class="flex items-center justify-between border-b border-zinc-200 pb-4 dark:border-zinc-800">
            <span class="font-mono text-xs font-bold text-rose-600 dark:text-rose-400">MODULE 01</span>
            <span class="font-mono text-[11px] text-zinc-500 dark:text-zinc-400">SCHEMA ENGINE</span>
          </div>
          <h3 class="mt-5 text-lg font-bold text-zinc-900 dark:text-white">
            Structured Copy Architect
          </h3>
          <p class="mt-2.5 text-sm leading-relaxed text-zinc-600 dark:text-zinc-300">
            Synthesizes your product brief, audience, and conversion goal into 10 distinct landing
            page sections with explicit illustrative disclaimers—never fabricating fake claims.
          </p>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-7 shadow-subtle transition hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
          <div class="flex items-center justify-between border-b border-zinc-200 pb-4 dark:border-zinc-800">
            <span class="font-mono text-xs font-bold text-rose-600 dark:text-rose-400">MODULE 02</span>
            <span class="font-mono text-[11px] text-zinc-500 dark:text-zinc-400">LIVE STUDIO</span>
          </div>
          <h3 class="mt-5 text-lg font-bold text-zinc-900 dark:text-white">
            Tactile Responsive Editor
          </h3>
          <p class="mt-2.5 text-sm leading-relaxed text-zinc-600 dark:text-zinc-300">
            Reorder sections, toggle visibility, fine-tune color tokens and typography pairings,
            upload validated media assets, and preview across Desktop, Tablet, and Mobile with
            30-step Undo/Redo.
          </p>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-7 shadow-subtle transition hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
          <div class="flex items-center justify-between border-b border-zinc-200 pb-4 dark:border-zinc-800">
            <span class="font-mono text-xs font-bold text-rose-600 dark:text-rose-400">MODULE 03</span>
            <span class="font-mono text-[11px] text-zinc-500 dark:text-zinc-400">EDGE DELIVERY</span>
          </div>
          <h3 class="mt-5 text-lg font-bold text-zinc-900 dark:text-white">
            Immutable Snapshot Publishing
          </h3>
          <p class="mt-2.5 text-sm leading-relaxed text-zinc-600 dark:text-zinc-300">
            Publish an immutable JSON snapshot to `/p/your-slug`. Keep iterating on drafts in the
            editor without breaking your live public conversion page until you republish.
          </p>
        </div>
      </div>
    </section>

    <!-- Editorial Bottom Banner (Adapts to Light & Dark Mode) -->
    <section id="workflow" class="border-t border-zinc-200 bg-zinc-100/80 px-4 py-20 text-zinc-900 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100 sm:px-6">
      <div class="mx-auto flex max-w-5xl flex-col items-center justify-between gap-8 text-center sm:flex-row sm:text-left">
        <div class="max-w-xl">
          <span class="font-mono text-xs uppercase tracking-[0.16em] text-rose-600 dark:text-rose-500">
            Ready for Launch
          </span>
          <h2 class="mt-2 text-3xl font-bold tracking-tight text-zinc-950 dark:text-white">
            Build your next product launch page today.
          </h2>
          <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
            Full access to the 3-step Copy Architect, 3 layout systems, and live responsive studio.
          </p>
        </div>
        <div class="flex shrink-0 gap-3">
          <RouterLink to="/register">
            <BaseButton size="lg" variant="primary">
              Create Studio Account →
            </BaseButton>
          </RouterLink>
        </div>
      </div>
    </section>
  </div>
</template>
