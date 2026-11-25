<script setup lang="ts">
import { computed } from 'vue';
import type { LandingPageContent, LandingPageTheme, SectionKey } from '@/types';
import { sanitizeHref } from '@/utils/validation';

const props = withDefaults(
  defineProps<{
    content: LandingPageContent;
    theme?: LandingPageTheme | null;
    interactiveEditor?: boolean;
    activeSection?: SectionKey | null;
  }>(),
  {
    theme: null,
    interactiveEditor: false,
    activeSection: null,
  }
);

const emit = defineEmits<{
  (e: 'select-section', key: SectionKey): void;
}>();

const palette = computed(() => ({
  primary: props.theme?.colors?.primary || '#111318',
  secondary: props.theme?.colors?.secondary || '#D94E28',
  accent: props.theme?.colors?.accent || '#15803D',
  background: props.theme?.colors?.background || '#F7F6F2',
  surface: props.theme?.colors?.surface || '#FFFFFF',
  text: props.theme?.colors?.text || '#111318',
}));

const orderedVisibleSections = computed(() => {
  const order = props.content?.sections_order || [
    'hero',
    'problem_solution',
    'features',
    'benefits',
    'social_proof',
    'testimonials',
    'pricing',
    'faq',
    'cta',
    'footer',
  ];
  return order.filter((key) => {
    const sec = props.content?.sections?.[key];
    return sec && sec.visible !== false;
  });
});

function handleSectionClick(key: SectionKey): void {
  if (props.interactiveEditor) {
    emit('select-section', key);
  }
}

function formatIndex(idx: number): string {
  return String(idx + 1).padStart(2, '0');
}
</script>

<template>
  <div
    class="landing-page-renderer min-h-full transition-colors duration-200"
    :style="{
      backgroundColor: palette.background,
      color: palette.text,
      fontFamily: theme?.typography?.body_font || 'Plus Jakarta Sans, Inter, sans-serif',
    }"
  >
    <template v-for="sectionKey in orderedVisibleSections" :key="sectionKey">
      <div
        class="relative transition-all"
        :class="[
          interactiveEditor ? 'cursor-pointer hover:ring-2 hover:ring-[#D94E28]/60' : '',
          interactiveEditor && activeSection === sectionKey ? 'ring-2 ring-[#D94E28]' : '',
        ]"
        @click="handleSectionClick(sectionKey)"
      >
        <!-- Editor section badge overlay -->
        <div
          v-if="interactiveEditor"
          class="pointer-events-none absolute right-3 top-3 z-10 rounded border border-white/20 bg-[#111318] px-2 py-0.5 font-mono text-[10px] font-semibold uppercase tracking-widest text-white"
        >
          {{ sectionKey.replace('_', ' ') }}
        </div>

        <!-- 1. HERO SECTION -->
        <section
          v-if="sectionKey === 'hero'"
          id="hero"
          class="relative overflow-hidden border-b border-black/10 px-6 py-20 sm:py-28 lg:px-8"
        >
          <div class="mx-auto max-w-5xl text-center">
            <span
              v-if="content.sections.hero.badge"
              class="inline-flex items-center gap-2 rounded-full border px-3.5 py-1 font-mono text-[11px] font-semibold uppercase tracking-[0.12em]"
              :style="{
                borderColor: `${palette.primary}30`,
                backgroundColor: palette.surface,
                color: palette.primary,
              }"
            >
              <span class="h-1.5 w-1.5 rounded-full" :style="{ backgroundColor: palette.secondary }" />
              {{ content.sections.hero.badge }}
            </span>

            <h1
              class="mx-auto mt-7 max-w-4xl text-3xl font-extrabold leading-[1.1] tracking-tight sm:text-5xl lg:text-6xl"
              :style="{ color: palette.text }"
            >
              {{ content.sections.hero.headline }}
            </h1>

            <p class="mx-auto mt-6 max-w-2xl text-base leading-relaxed opacity-80 sm:text-lg">
              {{ content.sections.hero.subheadline }}
            </p>

            <div class="mt-9 flex flex-wrap items-center justify-center gap-3.5">
              <a
                :href="sanitizeHref(content.sections.hero.primary_cta_url, '#pricing')"
                class="inline-flex items-center gap-2 rounded-lg px-6 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:opacity-90"
                :style="{ backgroundColor: palette.primary }"
              >
                <span>{{ content.sections.hero.primary_cta_text }}</span>
                <span aria-hidden="true">→</span>
              </a>

              <a
                v-if="content.sections.hero.secondary_cta_text"
                :href="sanitizeHref(content.sections.hero.secondary_cta_url, '#features')"
                class="inline-flex items-center gap-2 rounded-lg border border-black/15 bg-white px-6 py-3.5 text-sm font-semibold text-neutral-900 transition hover:bg-neutral-50"
              >
                {{ content.sections.hero.secondary_cta_text }}
              </a>
            </div>

            <!-- Highlights Bar -->
            <div
              v-if="content.sections.hero.highlights?.length"
              class="mt-12 flex flex-wrap items-center justify-center gap-x-8 gap-y-2 border-t border-black/10 pt-6 font-mono text-xs opacity-75"
            >
              <div
                v-for="(hl, idx) in content.sections.hero.highlights"
                :key="idx"
                class="flex items-center gap-2"
              >
                <span class="h-1.5 w-1.5 rounded-full" :style="{ backgroundColor: palette.accent }" />
                <span>{{ hl }}</span>
              </div>
            </div>

            <!-- Optional Hero Image -->
            <div
              v-if="content.sections.hero.image_url"
              class="mx-auto mt-12 max-w-4xl overflow-hidden rounded-xl border border-black/15 bg-white shadow-xl"
            >
              <img
                :src="sanitizeHref(content.sections.hero.image_url, '')"
                :alt="content.sections.hero.headline"
                class="max-h-[460px] w-full object-cover"
              />
            </div>
          </div>
        </section>

        <!-- 2. SOCIAL PROOF SECTION -->
        <section
          v-else-if="sectionKey === 'social_proof'"
          id="social-proof"
          class="border-b border-black/10 px-6 py-10"
          :style="{ backgroundColor: palette.surface }"
        >
          <div class="mx-auto max-w-5xl text-center">
            <p class="font-mono text-[11px] font-semibold uppercase tracking-[0.16em] opacity-60">
              {{ content.sections.social_proof.title }}
            </p>
            <div class="mt-6 flex flex-wrap items-center justify-center gap-8 sm:gap-14">
              <span
                v-for="(logo, idx) in content.sections.social_proof.logos"
                :key="idx"
                class="text-sm font-bold tracking-tight opacity-55 sm:text-base"
              >
                {{ logo }}
              </span>
            </div>
            <p
              v-if="content.sections.social_proof.is_illustrative"
              class="mt-4 inline-block rounded border border-amber-300/80 bg-amber-50/90 px-3 py-1 font-mono text-[11px] text-amber-900"
            >
              Note: {{ content.sections.social_proof.disclaimer }}
            </p>
          </div>
        </section>

        <!-- 3. PROBLEM & SOLUTION SECTION -->
        <section
          v-else-if="sectionKey === 'problem_solution'"
          id="problem-solution"
          class="border-b border-black/10 px-6 py-20 lg:px-8"
        >
          <div class="mx-auto max-w-6xl">
            <div class="max-w-2xl">
              <span class="font-mono text-xs font-semibold uppercase tracking-widest" :style="{ color: palette.secondary }">
                Contrast Matrix
              </span>
              <h2 class="mt-2 text-2xl font-extrabold tracking-tight sm:text-4xl">
                {{ content.sections.problem_solution.title }}
              </h2>
              <p class="mt-3 text-sm opacity-75 sm:text-base">
                {{ content.sections.problem_solution.subtitle }}
              </p>
            </div>

            <div class="mt-12 grid gap-6 md:grid-cols-2">
              <!-- Problem Column -->
              <div class="rounded-xl border border-black/10 bg-white/60 p-7">
                <div class="flex items-center justify-between border-b border-black/10 pb-3">
                  <h3 class="text-sm font-bold uppercase tracking-wider text-rose-800">
                    {{ content.sections.problem_solution.problem_title }}
                  </h3>
                  <span class="font-mono text-[11px] text-rose-700">LEGACY</span>
                </div>
                <ul class="mt-5 space-y-3.5">
                  <li
                    v-for="(item, idx) in content.sections.problem_solution.problem_items"
                    :key="idx"
                    class="flex items-start gap-3 text-sm opacity-85"
                  >
                    <span class="mt-1 font-mono text-xs font-bold text-rose-600">×</span>
                    <span>{{ item }}</span>
                  </li>
                </ul>
              </div>

              <!-- Solution Column -->
              <div
                class="rounded-xl border bg-white p-7 shadow-sm"
                :style="{ borderColor: palette.primary }"
              >
                <div class="flex items-center justify-between border-b border-black/10 pb-3">
                  <h3 class="text-sm font-bold uppercase tracking-wider" :style="{ color: palette.primary }">
                    {{ content.sections.problem_solution.solution_title }}
                  </h3>
                  <span class="font-mono text-[11px]" :style="{ color: palette.accent }">ENGINEERED</span>
                </div>
                <ul class="mt-5 space-y-3.5">
                  <li
                    v-for="(item, idx) in content.sections.problem_solution.solution_items"
                    :key="idx"
                    class="flex items-start gap-3 text-sm font-medium"
                  >
                    <span class="mt-0.5 font-mono text-xs font-bold" :style="{ color: palette.accent }">✓</span>
                    <span>{{ item }}</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </section>

        <!-- 4. FEATURES SECTION (NUMBERED ARCHITECTURAL CARDS — NO AI CLICHÉ ICONS) -->
        <section
          v-else-if="sectionKey === 'features'"
          id="features"
          class="border-b border-black/10 px-6 py-20 lg:px-8"
          :style="{ backgroundColor: palette.surface }"
        >
          <div class="mx-auto max-w-6xl">
            <div class="max-w-2xl">
              <span class="font-mono text-xs font-semibold uppercase tracking-widest" :style="{ color: palette.secondary }">
                System Capabilities
              </span>
              <h2 class="mt-2 text-2xl font-extrabold tracking-tight sm:text-4xl">
                {{ content.sections.features.title }}
              </h2>
              <p class="mt-3 text-sm opacity-75 sm:text-base">
                {{ content.sections.features.subtitle }}
              </p>
            </div>

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
              <div
                v-for="(feat, idx) in content.sections.features.items"
                :key="idx"
                class="flex flex-col justify-between rounded-xl border border-black/10 bg-white p-6 transition hover:border-black/30"
              >
                <div>
                  <div class="flex items-center justify-between border-b border-black/10 pb-3">
                    <span class="font-mono text-xs font-bold" :style="{ color: palette.secondary }">
                      {{ formatIndex(idx) }}
                    </span>
                    <span class="h-1.5 w-1.5 rounded-full" :style="{ backgroundColor: palette.primary }" />
                  </div>
                  <h3 class="mt-5 text-base font-bold text-neutral-900">
                    {{ feat.title }}
                  </h3>
                  <p class="mt-2 text-sm leading-relaxed text-neutral-600">
                    {{ feat.description }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- 5. BENEFITS SECTION -->
        <section
          v-else-if="sectionKey === 'benefits'"
          id="benefits"
          class="border-b border-black/10 px-6 py-20 lg:px-8"
        >
          <div class="mx-auto max-w-6xl">
            <div class="max-w-2xl">
              <span class="font-mono text-xs font-semibold uppercase tracking-widest" :style="{ color: palette.accent }">
                Operational Impact
              </span>
              <h2 class="mt-2 text-2xl font-extrabold tracking-tight sm:text-4xl">
                {{ content.sections.benefits.title }}
              </h2>
              <p class="mt-3 text-sm opacity-75 sm:text-base">
                {{ content.sections.benefits.subtitle }}
              </p>
            </div>

            <div class="mt-12 grid gap-6 md:grid-cols-3">
              <div
                v-for="(benefit, idx) in content.sections.benefits.items"
                :key="idx"
                class="rounded-xl border border-black/10 bg-white p-7"
              >
                <span
                  class="inline-block rounded border px-2.5 py-0.5 font-mono text-[11px] font-semibold uppercase"
                  :style="{
                    borderColor: `${palette.accent}40`,
                    color: palette.accent,
                  }"
                >
                  {{ benefit.metric_label }}
                </span>
                <h3 class="mt-4 text-lg font-bold text-neutral-900">
                  {{ benefit.title }}
                </h3>
                <p class="mt-2 text-sm leading-relaxed text-neutral-600">
                  {{ benefit.description }}
                </p>
              </div>
            </div>
          </div>
        </section>

        <!-- 6. TESTIMONIALS SECTION -->
        <section
          v-else-if="sectionKey === 'testimonials'"
          id="testimonials"
          class="border-b border-black/10 px-6 py-20 lg:px-8"
          :style="{ backgroundColor: palette.surface }"
        >
          <div class="mx-auto max-w-6xl">
            <div class="max-w-2xl">
              <h2 class="text-2xl font-extrabold tracking-tight sm:text-4xl">
                {{ content.sections.testimonials.title }}
              </h2>
              <p class="mt-2 text-sm opacity-75">
                {{ content.sections.testimonials.subtitle }}
              </p>
              <div
                v-if="content.sections.testimonials.is_illustrative"
                class="mt-4 inline-block rounded border border-amber-300 bg-amber-50 px-3.5 py-2 font-mono text-xs text-amber-900"
              >
                {{ content.sections.testimonials.disclaimer }}
              </div>
            </div>

            <div class="mt-12 grid gap-6 md:grid-cols-3">
              <div
                v-for="(item, idx) in content.sections.testimonials.items"
                :key="idx"
                class="flex flex-col justify-between rounded-xl border border-black/10 bg-white p-6"
              >
                <p class="font-serif text-lg italic leading-relaxed text-neutral-800">
                  "{{ item.quote }}"
                </p>
                <div class="mt-6 border-t border-black/10 pt-4">
                  <p class="text-xs font-bold text-neutral-900">{{ item.author_name }}</p>
                  <p class="font-mono text-[11px] text-neutral-500">
                    {{ item.author_role }} • {{ item.company }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- 7. PRICING SECTION -->
        <section
          v-else-if="sectionKey === 'pricing'"
          id="pricing"
          class="border-b border-black/10 px-6 py-20 lg:px-8"
        >
          <div class="mx-auto max-w-6xl">
            <div class="max-w-2xl">
              <h2 class="text-2xl font-extrabold tracking-tight sm:text-4xl">
                {{ content.sections.pricing.title }}
              </h2>
              <p class="mt-2 text-sm opacity-75">
                {{ content.sections.pricing.subtitle }}
              </p>
              <div
                v-if="content.sections.pricing.is_illustrative"
                class="mt-4 inline-block rounded border border-amber-300 bg-amber-50 px-3.5 py-2 font-mono text-xs text-amber-900"
              >
                {{ content.sections.pricing.disclaimer }}
              </div>
            </div>

            <div class="mt-12 grid gap-6 md:grid-cols-3">
              <div
                v-for="(plan, idx) in content.sections.pricing.plans"
                :key="idx"
                class="relative flex flex-col justify-between rounded-xl border bg-white p-7"
                :class="plan.is_popular ? 'ring-2' : 'border-black/15'"
                :style="plan.is_popular ? { borderColor: palette.primary } : {}"
              >
                <div>
                  <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-neutral-900">{{ plan.name }}</h3>
                    <span
                      v-if="plan.is_popular"
                      class="rounded px-2 py-0.5 font-mono text-[10px] font-bold uppercase text-white"
                      :style="{ backgroundColor: palette.secondary }"
                    >
                      Recommended
                    </span>
                  </div>
                  <p class="mt-2 text-xs text-neutral-500">{{ plan.description }}</p>
                  <div class="mt-5 flex items-baseline gap-1 border-b border-black/10 pb-5">
                    <span class="font-mono text-3xl font-extrabold text-neutral-900">{{ plan.price }}</span>
                    <span class="font-mono text-xs text-neutral-500">{{ plan.period }}</span>
                  </div>

                  <ul class="mt-5 space-y-2.5">
                    <li
                      v-for="(feat, fIdx) in plan.features"
                      :key="fIdx"
                      class="flex items-start gap-2.5 text-xs text-neutral-700"
                    >
                      <span class="font-mono font-bold" :style="{ color: palette.accent }">✓</span>
                      <span>{{ feat }}</span>
                    </li>
                  </ul>
                </div>

                <a
                  :href="sanitizeHref(plan.cta_url, '#cta')"
                  class="mt-8 block w-full rounded-lg py-2.5 text-center text-xs font-semibold transition"
                  :class="
                    plan.is_popular
                      ? 'text-white shadow-sm'
                      : 'border border-neutral-300 text-neutral-900 hover:bg-neutral-50'
                  "
                  :style="plan.is_popular ? { backgroundColor: palette.primary } : {}"
                >
                  {{ plan.cta_text }} →
                </a>
              </div>
            </div>
          </div>
        </section>

        <!-- 8. FAQ SECTION -->
        <section
          v-else-if="sectionKey === 'faq'"
          id="faq"
          class="border-b border-black/10 px-6 py-20 lg:px-8"
          :style="{ backgroundColor: palette.surface }"
        >
          <div class="mx-auto max-w-4xl">
            <div>
              <span class="font-mono text-xs font-semibold uppercase tracking-widest" :style="{ color: palette.secondary }">
                Specifications & FAQ
              </span>
              <h2 class="mt-2 text-2xl font-extrabold tracking-tight sm:text-3xl">
                {{ content.sections.faq.title }}
              </h2>
              <p class="mt-2 text-sm opacity-75">
                {{ content.sections.faq.subtitle }}
              </p>
            </div>

            <div class="mt-10 divide-y divide-black/10 border-t border-black/10">
              <div
                v-for="(faq, idx) in content.sections.faq.items"
                :key="idx"
                class="py-5"
              >
                <h3 class="flex items-baseline gap-3 text-sm font-bold text-neutral-900">
                  <span class="font-mono text-xs" :style="{ color: palette.secondary }">
                    {{ formatIndex(idx) }}
                  </span>
                  <span>{{ faq.question }}</span>
                </h3>
                <p class="mt-2 pl-7 text-sm leading-relaxed text-neutral-600">
                  {{ faq.answer }}
                </p>
              </div>
            </div>
          </div>
        </section>

        <!-- 9. CTA SECTION -->
        <section
          v-else-if="sectionKey === 'cta'"
          id="cta"
          class="px-6 py-20"
        >
          <div
            class="mx-auto max-w-5xl rounded-2xl px-6 py-16 text-center text-white shadow-xl sm:px-12"
            :style="{ backgroundColor: palette.primary }"
          >
            <h2 class="mx-auto max-w-2xl text-2xl font-extrabold tracking-tight sm:text-4xl">
              {{ content.sections.cta.headline }}
            </h2>
            <p class="mx-auto mt-4 max-w-xl text-sm text-white/80 sm:text-base">
              {{ content.sections.cta.subheadline }}
            </p>
            <div class="mt-8">
              <a
                :href="sanitizeHref(content.sections.cta.button_url, '#hero')"
                class="inline-flex items-center gap-2 rounded-lg bg-white px-7 py-3.5 text-sm font-bold text-neutral-900 shadow-sm transition hover:bg-neutral-100"
              >
                <span>{{ content.sections.cta.button_text }}</span>
                <span aria-hidden="true">→</span>
              </a>
            </div>
          </div>
        </section>

        <!-- 10. FOOTER SECTION -->
        <footer
          v-else-if="sectionKey === 'footer'"
          id="footer"
          class="border-t border-black/10 bg-[#111318] px-6 py-12 text-[#A1A09A]"
        >
          <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-6 sm:flex-row">
            <div>
              <p class="text-sm font-bold text-white">
                {{ content.sections.footer.brand_name }}
              </p>
              <p class="mt-1 text-xs text-[#8E8D87]">
                {{ content.sections.footer.tagline }}
              </p>
            </div>
            <div class="flex flex-wrap gap-6 font-mono text-xs">
              <a
                v-for="(link, idx) in content.sections.footer.links"
                :key="idx"
                :href="sanitizeHref(link.url, '#')"
                class="text-[#A1A09A] transition hover:text-white"
              >
                {{ link.label }}
              </a>
            </div>
          </div>
          <div class="mx-auto mt-8 max-w-6xl border-t border-white/10 pt-6 text-center font-mono text-[11px] text-[#787772]">
            {{ content.sections.footer.copyright_text }}
          </div>
        </footer>
      </div>
    </template>
  </div>
</template>
