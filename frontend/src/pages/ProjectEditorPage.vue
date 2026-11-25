<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useProjectStore } from '@/stores/projects';
import { useEditorStore } from '@/stores/editor';
import { useToastStore } from '@/stores/toast';
import { apiClient, ensureCsrfCookie, extractErrorMessage } from '@/services/api';
import type { ApiResponse, MediaAsset, SectionKey } from '@/types';
import LandingPageRenderer from '@/components/landing/LandingPageRenderer.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import BaseInput from '@/components/ui/BaseInput.vue';
import BaseTextarea from '@/components/ui/BaseTextarea.vue';
import BaseBadge from '@/components/ui/BaseBadge.vue';
import BaseModal from '@/components/ui/BaseModal.vue';
import {
  ArrowLeft,
  Monitor,
  Tablet,
  Smartphone,
  Undo2,
  Redo2,
  Eye,
  EyeOff,
  ArrowUp,
  ArrowDown,
  Plus,
  Trash2,
  ArrowUpRight,
  Upload,
} from 'lucide-vue-next';

const route = useRoute();
const router = useRouter();
const projectStore = useProjectStore();
const editorStore = useEditorStore();
const toastStore = useToastStore();

const activeTab = ref<'sections' | 'theme' | 'seo' | 'media'>('sections');
const showPublishModal = ref(false);
const publishSlug = ref('');
const publishing = ref(false);

const mediaAssets = ref<MediaAsset[]>([]);
const uploadingMedia = ref(false);
const mediaUploadError = ref('');

const projectId = computed(() => Number(route.params.id));

const viewportWidthLabel = computed(() => {
  if (editorStore.viewport === 'mobile') return '375px • Mobile';
  if (editorStore.viewport === 'tablet') return '768px • Tablet';
  return '1280px • Desktop';
});

onMounted(async () => {
  await Promise.all([
    projectStore.fetchTemplates(),
    loadProject(),
    loadMediaAssets(),
  ]);
});

async function loadProject(): Promise<void> {
  try {
    const project = await projectStore.fetchProject(projectId.value);
    editorStore.loadFromProject(project);
    publishSlug.value = project.slug;
  } catch (err) {
    toastStore.error('Could not load project', extractErrorMessage(err));
    router.push('/projects');
  }
}

async function loadMediaAssets(): Promise<void> {
  try {
    const response = await apiClient.get<ApiResponse<MediaAsset[]>>('/media');
    mediaAssets.value = response.data.data;
  } catch {
    // Non-blocking
  }
}

async function handleSave(): Promise<void> {
  try {
    const updated = await editorStore.saveChanges();
    if (updated) {
      projectStore.currentProject = updated;
      toastStore.success('Saved', 'Schema and theme tokens updated.');
    }
  } catch (err) {
    toastStore.error('Save failed', extractErrorMessage(err));
  }
}

async function handleTemplateSwitch(event: Event): Promise<void> {
  const select = event.target as HTMLSelectElement;
  const tplId = Number(select.value);
  if (!tplId || !projectStore.currentProject) return;

  try {
    await handleSave();
    const updated = await projectStore.applyTemplate(projectStore.currentProject.id, tplId, false);
    editorStore.loadFromProject(updated);
    toastStore.success('Layout updated', 'Palette and typography switched without losing copy.');
  } catch (err) {
    toastStore.error('Layout switch failed', extractErrorMessage(err));
  }
}

async function handlePublishAction(publish: boolean): Promise<void> {
  if (!projectStore.currentProject) return;
  publishing.value = true;
  try {
    await editorStore.saveChanges();
    const updated = await projectStore.publishProject(
      projectStore.currentProject.id,
      publish,
      publishSlug.value
    );
    publishSlug.value = updated.slug;
    toastStore.success(
      publish ? 'Published Live' : 'Reverted to Draft',
      publish ? `Endpoint active at /p/${updated.slug}` : 'Public endpoint disabled.'
    );
    showPublishModal.value = false;
  } catch (err) {
    toastStore.error('Publishing action failed', extractErrorMessage(err));
  } finally {
    publishing.value = false;
  }
}

async function handleImageUpload(event: Event): Promise<void> {
  const input = event.target as HTMLInputElement;
  const file = input.files?.[0];
  if (!file) return;

  mediaUploadError.value = '';
  uploadingMedia.value = true;
  try {
    await ensureCsrfCookie();
    const formData = new FormData();
    formData.append('file', file);
    if (projectStore.currentProject) {
      formData.append('project_id', String(projectStore.currentProject.id));
    }

    const response = await apiClient.post<ApiResponse<MediaAsset>>('/media', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });

    const uploaded = response.data.data;
    mediaAssets.value.unshift(uploaded);
    if (editorStore.content) {
      editorStore.content.sections.hero.image_url = uploaded.url;
      editorStore.recordSnapshot();
    }
    toastStore.success('Asset uploaded', 'Applied to Hero section.');
  } catch (err) {
    mediaUploadError.value = extractErrorMessage(err, 'Only JPG, PNG, WEBP up to 2MB are allowed.');
  } finally {
    uploadingMedia.value = false;
    input.value = '';
  }
}

function selectHeroImage(url: string): void {
  if (!editorStore.content) return;
  editorStore.content.sections.hero.image_url = url;
  editorStore.recordSnapshot();
  toastStore.success('Hero asset linked', 'Save changes to persist.');
}

function addFeatureItem(): void {
  if (!editorStore.content) return;
  editorStore.content.sections.features.items.push({
    icon: String(editorStore.content.sections.features.items.length + 1).padStart(2, '0'),
    title: 'New Capability',
    description: 'Describe how this capability helps your users.',
  });
  editorStore.recordSnapshot();
}

function removeFeatureItem(index: number): void {
  if (!editorStore.content) return;
  editorStore.content.sections.features.items.splice(index, 1);
  editorStore.recordSnapshot();
}

function addFaqItem(): void {
  if (!editorStore.content) return;
  editorStore.content.sections.faq.items.push({
    question: 'New specification question?',
    answer: 'Clear, direct answer for prospective buyers.',
  });
  editorStore.recordSnapshot();
}

function removeFaqItem(index: number): void {
  if (!editorStore.content) return;
  editorStore.content.sections.faq.items.splice(index, 1);
  editorStore.recordSnapshot();
}

const sectionLabels: Record<SectionKey, string> = {
  hero: 'Hero Statement',
  problem_solution: 'Contrast Matrix',
  features: 'Capabilities Grid',
  benefits: 'Operational Impact',
  social_proof: 'Partner Proof',
  testimonials: 'Customer Quotes',
  pricing: 'Pricing Tiers',
  faq: 'Specifications / FAQ',
  cta: 'Conversion Banner',
  footer: 'Footer',
};
</script>

<template>
  <div class="flex h-screen flex-col overflow-hidden bg-[#F4F4F5] text-zinc-900 select-none">
    <!-- Top Framer/Webflow-Grade Studio Header (48px) -->
    <header class="flex h-12 shrink-0 items-center justify-between border-b border-zinc-200 bg-white px-3">
      <!-- Left: Back, Project Title, Layout Switcher -->
      <div class="flex items-center gap-2.5">
        <RouterLink
          to="/projects"
          class="inline-flex h-7 items-center gap-1.5 rounded border border-zinc-200 bg-zinc-50 px-2 text-xs font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900"
        >
          <ArrowLeft class="h-3.5 w-3.5" />
          <span>Projects</span>
        </RouterLink>

        <div class="h-4 w-px bg-zinc-200" />

        <div v-if="projectStore.currentProject" class="flex items-center gap-2">
          <span class="max-w-[160px] truncate text-xs font-semibold text-zinc-900 sm:max-w-[220px]">
            {{ projectStore.currentProject.name }}
          </span>
          <BaseBadge :variant="projectStore.currentProject.status">
            {{ projectStore.currentProject.status }}
          </BaseBadge>
        </div>

        <select
          v-if="projectStore.currentProject"
          :value="projectStore.currentProject.template_id"
          aria-label="Switch layout system"
          class="hidden h-7 rounded border border-zinc-200 bg-zinc-50 px-2 text-xs font-medium text-zinc-700 focus:border-zinc-900 focus:outline-none md:block"
          @change="handleTemplateSwitch"
        >
          <option
            v-for="tpl in projectStore.templates"
            :key="tpl.id"
            :value="tpl.id"
          >
            Layout: {{ tpl.name }}
          </option>
        </select>
      </div>

      <!-- Center: Breakpoint Pill Bar & Undo/Redo -->
      <div class="hidden items-center gap-2 sm:flex">
        <div class="flex items-center rounded-md border border-zinc-200 bg-zinc-100/80 p-0.5">
          <button
            type="button"
            class="flex h-6 items-center gap-1 rounded px-2 text-[11px] font-medium transition"
            :class="
              editorStore.viewport === 'desktop'
                ? 'bg-white text-zinc-950 shadow-2xs'
                : 'text-zinc-500 hover:text-zinc-800'
            "
            title="Desktop Breakpoint"
            @click="editorStore.viewport = 'desktop'"
          >
            <Monitor class="h-3.5 w-3.5" />
            <span class="hidden lg:inline">1280</span>
          </button>
          <button
            type="button"
            class="flex h-6 items-center gap-1 rounded px-2 text-[11px] font-medium transition"
            :class="
              editorStore.viewport === 'tablet'
                ? 'bg-white text-zinc-950 shadow-2xs'
                : 'text-zinc-500 hover:text-zinc-800'
            "
            title="Tablet Breakpoint (768px)"
            @click="editorStore.viewport = 'tablet'"
          >
            <Tablet class="h-3.5 w-3.5" />
            <span class="hidden lg:inline">768</span>
          </button>
          <button
            type="button"
            class="flex h-6 items-center gap-1 rounded px-2 text-[11px] font-medium transition"
            :class="
              editorStore.viewport === 'mobile'
                ? 'bg-white text-zinc-950 shadow-2xs'
                : 'text-zinc-500 hover:text-zinc-800'
            "
            title="Mobile Breakpoint (375px)"
            @click="editorStore.viewport = 'mobile'"
          >
            <Smartphone class="h-3.5 w-3.5" />
            <span class="hidden lg:inline">375</span>
          </button>
        </div>

        <div class="flex items-center rounded-md border border-zinc-200 bg-white p-0.5">
          <button
            type="button"
            :disabled="!editorStore.canUndo"
            class="rounded p-1 text-zinc-600 hover:bg-zinc-100 disabled:opacity-30"
            title="Undo"
            aria-label="Undo"
            @click="editorStore.undo"
          >
            <Undo2 class="h-3.5 w-3.5" />
          </button>
          <button
            type="button"
            :disabled="!editorStore.canRedo"
            class="rounded p-1 text-zinc-600 hover:bg-zinc-100 disabled:opacity-30"
            title="Redo"
            aria-label="Redo"
            @click="editorStore.redo"
          >
            <Redo2 class="h-3.5 w-3.5" />
          </button>
        </div>
      </div>

      <!-- Right: Save & Publish -->
      <div class="flex items-center gap-1.5">
        <RouterLink
          v-if="projectStore.currentProject?.status === 'published'"
          :to="`/p/${projectStore.currentProject.slug}`"
          target="_blank"
          class="hidden h-7 items-center gap-1 rounded border border-zinc-200 px-2 font-mono text-[11px] text-zinc-600 hover:bg-zinc-50 md:inline-flex"
        >
          <span>/p/{{ projectStore.currentProject.slug }}</span>
          <ArrowUpRight class="h-3 w-3" />
        </RouterLink>

        <BaseButton
          size="sm"
          variant="outline"
          :loading="editorStore.saving"
          @click="handleSave"
        >
          <span>{{ editorStore.isDirty ? 'Save*' : 'Saved' }}</span>
        </BaseButton>

        <BaseButton
          size="sm"
          variant="primary"
          @click="showPublishModal = true"
        >
          <span>Publish</span>
        </BaseButton>
      </div>
    </header>

    <!-- Main Studio Split Area -->
    <div v-if="editorStore.content && editorStore.theme" class="flex flex-1 overflow-hidden">
      <!-- Left Inspector Panel (330px) -->
      <aside class="flex w-80 shrink-0 flex-col border-r border-zinc-200 bg-white sm:w-[340px]">
        <!-- Segmented Inspector Tabs -->
        <div class="grid grid-cols-4 border-b border-zinc-200 bg-zinc-50/80 p-1 font-mono text-[11px] font-medium">
          <button
            v-for="tab in (['sections', 'theme', 'media', 'seo'] as const)"
            :key="tab"
            type="button"
            class="rounded py-1.5 uppercase tracking-wider transition"
            :class="
              activeTab === tab
                ? 'bg-zinc-900 text-white shadow-2xs'
                : 'text-zinc-500 hover:text-zinc-900'
            "
            @click="activeTab = tab"
          >
            {{ tab }}
          </button>
        </div>

        <!-- Scrollable Property Inspector -->
        <div class="flex-1 overflow-y-auto p-3.5">
          <!-- TAB 1: LAYERS & SECTION PROPERTIES -->
          <div v-if="activeTab === 'sections'" class="space-y-5">
            <div>
              <div class="mb-2 flex items-center justify-between">
                <span class="font-mono text-[10px] font-semibold uppercase tracking-wider text-zinc-400">
                  Layer Stack (10)
                </span>
                <span class="font-mono text-[10px] text-zinc-400">ORDER / VISIBILITY</span>
              </div>
              <div class="divide-y divide-zinc-100 overflow-hidden rounded-md border border-zinc-200">
                <div
                  v-for="(secKey, idx) in editorStore.content.sections_order"
                  :key="secKey"
                  class="flex items-center justify-between px-2.5 py-1.5 text-xs transition"
                  :class="
                    editorStore.activeSection === secKey
                      ? 'bg-zinc-900 text-white font-medium'
                      : 'bg-white text-zinc-700 hover:bg-zinc-50'
                  "
                >
                  <button
                    type="button"
                    class="flex flex-1 items-center gap-2 text-left"
                    @click="editorStore.activeSection = secKey"
                  >
                    <span
                      class="font-mono text-[10px]"
                      :class="editorStore.activeSection === secKey ? 'text-zinc-400' : 'text-zinc-400'"
                    >
                      {{ String(idx + 1).padStart(2, '0') }}
                    </span>
                    <span class="truncate">{{ sectionLabels[secKey] || secKey }}</span>
                  </button>

                  <div class="flex items-center gap-0.5">
                    <button
                      type="button"
                      :disabled="idx === 0"
                      class="rounded p-1 opacity-70 hover:opacity-100 disabled:opacity-20"
                      title="Move up"
                      @click="editorStore.moveSection(secKey, 'up')"
                    >
                      <ArrowUp class="h-3 w-3" />
                    </button>
                    <button
                      type="button"
                      :disabled="idx === editorStore.content.sections_order.length - 1"
                      class="rounded p-1 opacity-70 hover:opacity-100 disabled:opacity-20"
                      title="Move down"
                      @click="editorStore.moveSection(secKey, 'down')"
                    >
                      <ArrowDown class="h-3 w-3" />
                    </button>
                    <button
                      type="button"
                      class="rounded p-1 opacity-80 hover:opacity-100"
                      :title="editorStore.content.sections[secKey].visible ? 'Hide layer' : 'Show layer'"
                      @click="editorStore.toggleSectionVisibility(secKey)"
                    >
                      <Eye v-if="editorStore.content.sections[secKey].visible" class="h-3 w-3" />
                      <EyeOff v-else class="h-3 w-3 opacity-50" />
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Active Section Property Form -->
            <div class="border-t border-zinc-200 pt-4">
              <div class="mb-3 flex items-center justify-between">
                <span class="font-mono text-[10px] font-semibold uppercase tracking-wider text-zinc-400">
                  Inspector
                </span>
                <span class="rounded bg-zinc-100 px-1.5 py-0.5 font-mono text-[10px] font-medium text-zinc-800">
                  {{ sectionLabels[editorStore.activeSection] }}
                </span>
              </div>

              <!-- HERO -->
              <div v-if="editorStore.activeSection === 'hero'" class="space-y-3">
                <BaseInput
                  v-model="editorStore.content.sections.hero.badge"
                  label="Eyebrow Badge"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <BaseInput
                  v-model="editorStore.content.sections.hero.headline"
                  label="Headline"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <BaseTextarea
                  v-model="editorStore.content.sections.hero.subheadline"
                  label="Subheadline"
                  :rows="3"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <div class="grid grid-cols-2 gap-2">
                  <BaseInput
                    v-model="editorStore.content.sections.hero.primary_cta_text"
                    label="Primary CTA"
                    @update:model-value="editorStore.recordSnapshot()"
                  />
                  <BaseInput
                    v-model="editorStore.content.sections.hero.primary_cta_url"
                    label="Primary Target"
                    @update:model-value="editorStore.recordSnapshot()"
                  />
                </div>
                <div class="grid grid-cols-2 gap-2">
                  <BaseInput
                    v-model="editorStore.content.sections.hero.secondary_cta_text"
                    label="Secondary CTA"
                    @update:model-value="editorStore.recordSnapshot()"
                  />
                  <BaseInput
                    v-model="editorStore.content.sections.hero.secondary_cta_url"
                    label="Secondary Target"
                    @update:model-value="editorStore.recordSnapshot()"
                  />
                </div>
                <BaseInput
                  v-model="editorStore.content.sections.hero.image_url"
                  label="Media Asset URL"
                  placeholder="https://... or select from Media tab"
                  @update:model-value="editorStore.recordSnapshot()"
                />
              </div>

              <!-- PROBLEM & SOLUTION -->
              <div v-else-if="editorStore.activeSection === 'problem_solution'" class="space-y-3">
                <BaseInput
                  v-model="editorStore.content.sections.problem_solution.title"
                  label="Section Heading"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <BaseTextarea
                  v-model="editorStore.content.sections.problem_solution.subtitle"
                  label="Subheading"
                  :rows="2"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <BaseInput
                  v-model="editorStore.content.sections.problem_solution.problem_title"
                  label="Legacy Column Label"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <BaseInput
                  v-model="editorStore.content.sections.problem_solution.solution_title"
                  label="Engineered Column Label"
                  @update:model-value="editorStore.recordSnapshot()"
                />
              </div>

              <!-- FEATURES -->
              <div v-else-if="editorStore.activeSection === 'features'" class="space-y-3">
                <BaseInput
                  v-model="editorStore.content.sections.features.title"
                  label="Section Heading"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <BaseTextarea
                  v-model="editorStore.content.sections.features.subtitle"
                  label="Subtitle"
                  :rows="2"
                  @update:model-value="editorStore.recordSnapshot()"
                />

                <div class="space-y-2.5">
                  <div
                    v-for="(item, fIdx) in editorStore.content.sections.features.items"
                    :key="fIdx"
                    class="rounded-md border border-zinc-200 bg-zinc-50/60 p-2.5 space-y-2"
                  >
                    <div class="flex items-center justify-between">
                      <span class="font-mono text-[10px] font-semibold text-zinc-500">
                        BLOCK 0{{ fIdx + 1 }}
                      </span>
                      <button
                        type="button"
                        class="text-zinc-400 hover:text-rose-600"
                        @click="removeFeatureItem(fIdx)"
                      >
                        <Trash2 class="h-3.5 w-3.5" />
                      </button>
                    </div>
                    <BaseInput
                      v-model="item.title"
                      label="Title"
                      @update:model-value="editorStore.recordSnapshot()"
                    />
                    <BaseTextarea
                      v-model="item.description"
                      label="Copy"
                      :rows="2"
                      @update:model-value="editorStore.recordSnapshot()"
                    />
                  </div>
                </div>
                <BaseButton size="sm" variant="outline" full-width @click="addFeatureItem">
                  <Plus class="h-3.5 w-3.5" />
                  <span>Add Capability Block</span>
                </BaseButton>
              </div>

              <!-- BENEFITS -->
              <div v-else-if="editorStore.activeSection === 'benefits'" class="space-y-3">
                <BaseInput
                  v-model="editorStore.content.sections.benefits.title"
                  label="Heading"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <BaseTextarea
                  v-model="editorStore.content.sections.benefits.subtitle"
                  label="Subtitle"
                  :rows="2"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <div
                  v-for="(bItem, bIdx) in editorStore.content.sections.benefits.items"
                  :key="bIdx"
                  class="rounded-md border border-zinc-200 bg-zinc-50/60 p-2.5 space-y-2"
                >
                  <BaseInput
                    v-model="bItem.metric_label"
                    label="Metric Tag"
                    @update:model-value="editorStore.recordSnapshot()"
                  />
                  <BaseInput
                    v-model="bItem.title"
                    label="Outcome Title"
                    @update:model-value="editorStore.recordSnapshot()"
                  />
                  <BaseTextarea
                    v-model="bItem.description"
                    label="Description"
                    :rows="2"
                    @update:model-value="editorStore.recordSnapshot()"
                  />
                </div>
              </div>

              <!-- SOCIAL PROOF -->
              <div v-else-if="editorStore.activeSection === 'social_proof'" class="space-y-3">
                <BaseInput
                  v-model="editorStore.content.sections.social_proof.title"
                  label="Section Label"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <div
                  v-for="(_, lIdx) in editorStore.content.sections.social_proof.logos"
                  :key="lIdx"
                >
                  <BaseInput
                    v-model="editorStore.content.sections.social_proof.logos[lIdx]"
                    :label="`Partner Wordmark #${lIdx + 1}`"
                    @update:model-value="editorStore.recordSnapshot()"
                  />
                </div>
              </div>

              <!-- TESTIMONIALS -->
              <div v-else-if="editorStore.activeSection === 'testimonials'" class="space-y-3">
                <BaseInput
                  v-model="editorStore.content.sections.testimonials.title"
                  label="Heading"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <div
                  v-for="(tItem, tIdx) in editorStore.content.sections.testimonials.items"
                  :key="tIdx"
                  class="rounded-md border border-zinc-200 bg-zinc-50/60 p-2.5 space-y-2"
                >
                  <BaseTextarea
                    v-model="tItem.quote"
                    label="Pull Quote"
                    :rows="2"
                    @update:model-value="editorStore.recordSnapshot()"
                  />
                  <BaseInput
                    v-model="tItem.author_name"
                    label="Attribution"
                    @update:model-value="editorStore.recordSnapshot()"
                  />
                  <BaseInput
                    v-model="tItem.company"
                    label="Organization"
                    @update:model-value="editorStore.recordSnapshot()"
                  />
                </div>
              </div>

              <!-- PRICING -->
              <div v-else-if="editorStore.activeSection === 'pricing'" class="space-y-3">
                <BaseInput
                  v-model="editorStore.content.sections.pricing.title"
                  label="Pricing Heading"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <div
                  v-for="(plan, pIdx) in editorStore.content.sections.pricing.plans"
                  :key="pIdx"
                  class="rounded-md border border-zinc-200 bg-zinc-50/60 p-2.5 space-y-2"
                >
                  <BaseInput
                    v-model="plan.name"
                    label="Tier Name"
                    @update:model-value="editorStore.recordSnapshot()"
                  />
                  <div class="grid grid-cols-2 gap-2">
                    <BaseInput
                      v-model="plan.price"
                      label="Price"
                      @update:model-value="editorStore.recordSnapshot()"
                    />
                    <BaseInput
                      v-model="plan.period"
                      label="Cadence"
                      @update:model-value="editorStore.recordSnapshot()"
                    />
                  </div>
                  <BaseInput
                    v-model="plan.cta_text"
                    label="Button Label"
                    @update:model-value="editorStore.recordSnapshot()"
                  />
                </div>
              </div>

              <!-- FAQ -->
              <div v-else-if="editorStore.activeSection === 'faq'" class="space-y-3">
                <BaseInput
                  v-model="editorStore.content.sections.faq.title"
                  label="Heading"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <div
                  v-for="(faq, qIdx) in editorStore.content.sections.faq.items"
                  :key="qIdx"
                  class="rounded-md border border-zinc-200 bg-zinc-50/60 p-2.5 space-y-2"
                >
                  <div class="flex items-center justify-between">
                    <span class="font-mono text-[10px] font-semibold text-zinc-500">
                      Q0{{ qIdx + 1 }}
                    </span>
                    <button
                      type="button"
                      class="text-zinc-400 hover:text-rose-600"
                      @click="removeFaqItem(qIdx)"
                    >
                      <Trash2 class="h-3.5 w-3.5" />
                    </button>
                  </div>
                  <BaseInput
                    v-model="faq.question"
                    label="Question"
                    @update:model-value="editorStore.recordSnapshot()"
                  />
                  <BaseTextarea
                    v-model="faq.answer"
                    label="Answer"
                    :rows="2"
                    @update:model-value="editorStore.recordSnapshot()"
                  />
                </div>
                <BaseButton size="sm" variant="outline" full-width @click="addFaqItem">
                  <Plus class="h-3.5 w-3.5" />
                  <span>Add FAQ Row</span>
                </BaseButton>
              </div>

              <!-- CTA -->
              <div v-else-if="editorStore.activeSection === 'cta'" class="space-y-3">
                <BaseInput
                  v-model="editorStore.content.sections.cta.headline"
                  label="Headline"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <BaseTextarea
                  v-model="editorStore.content.sections.cta.subheadline"
                  label="Subheadline"
                  :rows="2"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <BaseInput
                  v-model="editorStore.content.sections.cta.button_text"
                  label="Button Label"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <BaseInput
                  v-model="editorStore.content.sections.cta.button_url"
                  label="Target URL"
                  @update:model-value="editorStore.recordSnapshot()"
                />
              </div>

              <!-- FOOTER -->
              <div v-else-if="editorStore.activeSection === 'footer'" class="space-y-3">
                <BaseInput
                  v-model="editorStore.content.sections.footer.brand_name"
                  label="Brand Wordmark"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <BaseInput
                  v-model="editorStore.content.sections.footer.tagline"
                  label="Tagline"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <BaseInput
                  v-model="editorStore.content.sections.footer.copyright_text"
                  label="Copyright Line"
                  @update:model-value="editorStore.recordSnapshot()"
                />
              </div>
            </div>
          </div>

          <!-- TAB 2: DESIGN TOKENS (THEME) -->
          <div v-else-if="activeTab === 'theme'" class="space-y-4">
            <span class="font-mono text-[10px] font-semibold uppercase tracking-wider text-zinc-400">
              Color Tokens
            </span>
            <div class="divide-y divide-zinc-100 rounded-md border border-zinc-200">
              <div
                v-for="(hex, colorKey) in editorStore.theme.colors"
                :key="colorKey"
                class="flex items-center justify-between px-3 py-2"
              >
                <div>
                  <p class="text-xs font-medium capitalize text-zinc-800">{{ colorKey }}</p>
                  <p class="font-mono text-[10px] text-zinc-400">{{ hex }}</p>
                </div>
                <input
                  type="color"
                  :value="hex"
                  :aria-label="`Choose ${colorKey} color`"
                  class="h-7 w-10 cursor-pointer rounded border border-zinc-200 bg-white p-0.5"
                  @input="editorStore.updateColor(colorKey, ($event.target as HTMLInputElement).value)"
                />
              </div>
            </div>

            <div class="border-t border-zinc-200 pt-4 space-y-2">
              <span class="font-mono text-[10px] font-semibold uppercase tracking-wider text-zinc-400">
                Typography Stack
              </span>
              <select
                :value="editorStore.theme.typography.body_font"
                class="h-8 w-full rounded-md border border-zinc-200 bg-white px-2.5 text-xs text-zinc-900"
                @change="
                  editorStore.updateTypography(
                    'body_font',
                    ($event.target as HTMLSelectElement).value
                  )
                "
              >
                <option value="Inter">Inter (Precision Sans)</option>
                <option value="Plus Jakarta Sans">Plus Jakarta Sans</option>
                <option value="Georgia, serif">Georgia (Editorial Serif)</option>
              </select>
            </div>
          </div>

          <!-- TAB 3: MEDIA ASSETS -->
          <div v-else-if="activeTab === 'media'" class="space-y-4">
            <div class="rounded-lg border border-dashed border-zinc-300 bg-zinc-50 p-4 text-center">
              <Upload class="mx-auto h-5 w-5 text-zinc-500" />
              <p class="mt-1.5 text-xs font-semibold text-zinc-900">Upload Image Asset</p>
              <p class="mt-0.5 font-mono text-[10px] text-zinc-400">
                JPG, PNG, WEBP • MAX 2MB
              </p>
              <label
                class="mt-3 inline-flex h-7 cursor-pointer items-center rounded-md bg-zinc-900 px-3 text-xs font-medium text-white hover:bg-zinc-800"
              >
                <span>{{ uploadingMedia ? 'Uploading...' : 'Select File' }}</span>
                <input
                  type="file"
                  accept="image/jpeg,image/png,image/webp"
                  class="hidden"
                  :disabled="uploadingMedia"
                  @change="handleImageUpload"
                />
              </label>
              <p v-if="mediaUploadError" role="alert" class="mt-2 text-xs text-rose-600">
                {{ mediaUploadError }}
              </p>
            </div>

            <div>
              <span class="font-mono text-[10px] font-semibold uppercase tracking-wider text-zinc-400">
                Workspace Library ({{ mediaAssets.length }})
              </span>
              <div v-if="mediaAssets.length === 0" class="py-6 text-center font-mono text-xs text-zinc-400">
                No uploaded assets.
              </div>
              <div v-else class="mt-2 grid grid-cols-2 gap-2">
                <div
                  v-for="asset in mediaAssets"
                  :key="asset.id"
                  class="overflow-hidden rounded border border-zinc-200 bg-zinc-50"
                >
                  <img
                    :src="asset.url"
                    :alt="asset.original_name"
                    class="h-20 w-full object-cover"
                  />
                  <button
                    type="button"
                    class="w-full bg-zinc-900 py-1 font-mono text-[10px] font-medium text-white hover:bg-zinc-800"
                    @click="selectHeroImage(asset.url)"
                  >
                    Bind to Hero
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- TAB 4: SEO METADATA -->
          <div v-else-if="activeTab === 'seo'" class="space-y-3">
            <BaseInput
              id="seo-title"
              v-model="editorStore.seoTitle"
              label="Meta Title (<title>)"
              placeholder="Product — Value Proposition"
              @update:model-value="editorStore.recordSnapshot()"
            />
            <BaseTextarea
              id="seo-description"
              v-model="editorStore.seoDescription"
              label="Meta Description"
              :rows="4"
              placeholder="150–160 character summary for search engines"
              @update:model-value="editorStore.recordSnapshot()"
            />
          </div>
        </div>
      </aside>

      <!-- Right Studio Canvas with Dot-Matrix & Ruler Bar -->
      <main class="bg-studio-canvas flex-1 overflow-y-auto p-4 sm:p-6">
        <div
          class="mx-auto overflow-hidden rounded-xl border border-zinc-300/90 bg-white shadow-elevated transition-all duration-200"
          :class="{
            'w-full': editorStore.viewport === 'desktop',
            'max-w-[768px]': editorStore.viewport === 'tablet',
            'max-w-[375px]': editorStore.viewport === 'mobile',
          }"
        >
          <!-- Simulated Browser Frame Header Inside Canvas -->
          <div class="flex h-8 items-center justify-between border-b border-zinc-200 bg-zinc-100/90 px-3 font-mono text-[11px] text-zinc-500">
            <div class="flex items-center gap-1.5">
              <span class="h-2 w-2 rounded-full bg-zinc-300" />
              <span class="h-2 w-2 rounded-full bg-zinc-300" />
              <span class="h-2 w-2 rounded-full bg-zinc-300" />
            </div>
            <span>/p/{{ projectStore.currentProject?.slug }}</span>
            <span>{{ viewportWidthLabel }}</span>
          </div>

          <LandingPageRenderer
            :content="editorStore.content"
            :theme="editorStore.theme"
            :interactive-editor="true"
            :active-section="editorStore.activeSection"
            @select-section="(sec) => (editorStore.activeSection = sec)"
          />
        </div>
      </main>
    </div>

    <!-- Publish Modal -->
    <BaseModal
      :open="showPublishModal"
      title="Publish Endpoint Configuration"
      max-width="lg"
      @close="showPublishModal = false"
    >
      <div v-if="projectStore.currentProject" class="space-y-4">
        <div class="flex items-center justify-between rounded-md border border-zinc-200 bg-zinc-50 p-3">
          <span class="font-mono text-xs text-zinc-500">Current State</span>
          <BaseBadge :variant="projectStore.currentProject.status">
            {{ projectStore.currentProject.status }}
          </BaseBadge>
        </div>

        <BaseInput
          id="publish-slug"
          v-model="publishSlug"
          label="Public Slug"
          placeholder="my-product-launch"
          hint="Served publicly at /p/{slug} when published."
        />

        <div
          v-if="projectStore.currentProject.status === 'published'"
          class="flex items-center justify-between rounded-md border border-emerald-200 bg-emerald-50/70 px-3.5 py-2.5 text-xs text-emerald-900"
        >
          <span class="font-mono">/p/{{ projectStore.currentProject.slug }}</span>
          <RouterLink
            :to="`/p/${projectStore.currentProject.slug}`"
            target="_blank"
            class="inline-flex items-center gap-1 font-semibold text-emerald-800 hover:underline"
          >
            <span>Open Live Endpoint</span>
            <ArrowUpRight class="h-3.5 w-3.5" />
          </RouterLink>
        </div>

        <div class="mt-6 flex items-center justify-between border-t border-zinc-100 pt-4">
          <BaseButton
            v-if="projectStore.currentProject.status === 'published'"
            variant="danger"
            :loading="publishing"
            @click="handlePublishAction(false)"
          >
            Unpublish
          </BaseButton>
          <div v-else />

          <div class="flex items-center gap-2">
            <BaseButton variant="outline" @click="showPublishModal = false">Close</BaseButton>
            <BaseButton
              variant="primary"
              :loading="publishing"
              @click="handlePublishAction(true)"
            >
              <span>
                {{
                  projectStore.currentProject.status === 'published'
                    ? 'Update Live Snapshot'
                    : 'Publish Endpoint →'
                }}
              </span>
            </BaseButton>
          </div>
        </div>
      </div>
    </BaseModal>
  </div>
</template>
