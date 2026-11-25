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
  Save,
  Globe,
  Eye,
  EyeOff,
  ArrowUp,
  ArrowDown,
  Palette,
  Layers,
  Search,
  Image as ImageIcon,
  Plus,
  Trash2,
  ExternalLink,
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
      toastStore.success('Changes saved', 'Your landing page has been updated.');
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
    toastStore.success('Template applied', 'Color palette and layout updated without losing your copy.');
  } catch (err) {
    toastStore.error('Template switch failed', extractErrorMessage(err));
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
      publish ? 'Landing Page Published!' : 'Page Unpublished',
      publish ? `Live at /p/${updated.slug}` : 'Page reverted to private draft.'
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
    toastStore.success('Image uploaded', 'Applied to Hero section and saved in Media Library.');
  } catch (err) {
    mediaUploadError.value = extractErrorMessage(err, 'Upload failed. Only JPG, PNG, WEBP up to 2MB are allowed.');
  } finally {
    uploadingMedia.value = false;
    input.value = '';
  }
}

function selectHeroImage(url: string): void {
  if (!editorStore.content) return;
  editorStore.content.sections.hero.image_url = url;
  editorStore.recordSnapshot();
  toastStore.success('Hero image updated', 'Remember to click Save to persist changes.');
}

function addFeatureItem(): void {
  if (!editorStore.content) return;
  editorStore.content.sections.features.items.push({
    icon: 'Sparkles',
    title: 'New Feature Capability',
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
    question: 'New frequently asked question?',
    answer: 'Clear, helpful answer for prospective customers.',
  });
  editorStore.recordSnapshot();
}

function removeFaqItem(index: number): void {
  if (!editorStore.content) return;
  editorStore.content.sections.faq.items.splice(index, 1);
  editorStore.recordSnapshot();
}

const sectionLabels: Record<SectionKey, string> = {
  hero: 'Hero Banner',
  problem_solution: 'Problem & Solution',
  features: 'Features Grid',
  benefits: 'Key Benefits',
  social_proof: 'Social Proof (Illustrative)',
  testimonials: 'Testimonials (Illustrative)',
  pricing: 'Pricing Tiers (Illustrative)',
  faq: 'FAQ Accordion',
  cta: 'Call to Action',
  footer: 'Footer',
};
</script>

<template>
  <div class="flex h-screen flex-col overflow-hidden bg-slate-100">
    <!-- Top Editor Toolbar -->
    <header class="flex h-16 shrink-0 items-center justify-between border-b border-brand-border bg-white px-4 shadow-2xs">
      <!-- Left: Back & Project Info -->
      <div class="flex items-center gap-3">
        <RouterLink
          to="/projects"
          class="flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100"
        >
          <ArrowLeft class="h-4 w-4" />
          <span>Projects</span>
        </RouterLink>

        <div class="h-5 w-px bg-slate-200" />

        <div v-if="projectStore.currentProject" class="flex items-center gap-2">
          <h1 class="max-w-[180px] truncate text-sm font-bold text-brand-text sm:max-w-xs">
            {{ projectStore.currentProject.name }}
          </h1>
          <BaseBadge :variant="projectStore.currentProject.status">
            {{ projectStore.currentProject.status }}
          </BaseBadge>
        </div>

        <!-- Template Quick Switcher -->
        <select
          v-if="projectStore.currentProject"
          :value="projectStore.currentProject.template_id"
          aria-label="Switch template"
          class="hidden rounded-lg border border-brand-border bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-700 md:block"
          @change="handleTemplateSwitch"
        >
          <option
            v-for="tpl in projectStore.templates"
            :key="tpl.id"
            :value="tpl.id"
          >
            Template: {{ tpl.name }}
          </option>
        </select>
      </div>

      <!-- Center: Viewport Switcher & Undo/Redo -->
      <div class="hidden items-center gap-2 sm:flex">
        <div class="flex items-center rounded-lg border border-brand-border bg-slate-50 p-1">
          <button
            type="button"
            class="rounded-md px-2.5 py-1 text-xs font-medium transition"
            :class="
              editorStore.viewport === 'desktop'
                ? 'bg-white text-brand-primary shadow-2xs'
                : 'text-slate-500 hover:text-slate-800'
            "
            title="Desktop Preview"
            @click="editorStore.viewport = 'desktop'"
          >
            <Monitor class="h-4 w-4" />
          </button>
          <button
            type="button"
            class="rounded-md px-2.5 py-1 text-xs font-medium transition"
            :class="
              editorStore.viewport === 'tablet'
                ? 'bg-white text-brand-primary shadow-2xs'
                : 'text-slate-500 hover:text-slate-800'
            "
            title="Tablet Preview (768px)"
            @click="editorStore.viewport = 'tablet'"
          >
            <Tablet class="h-4 w-4" />
          </button>
          <button
            type="button"
            class="rounded-md px-2.5 py-1 text-xs font-medium transition"
            :class="
              editorStore.viewport === 'mobile'
                ? 'bg-white text-brand-primary shadow-2xs'
                : 'text-slate-500 hover:text-slate-800'
            "
            title="Mobile Preview (375px)"
            @click="editorStore.viewport = 'mobile'"
          >
            <Smartphone class="h-4 w-4" />
          </button>
        </div>

        <div class="flex items-center rounded-lg border border-brand-border bg-white p-1">
          <button
            type="button"
            :disabled="!editorStore.canUndo"
            class="rounded p-1.5 text-slate-600 hover:bg-slate-100 disabled:opacity-35"
            title="Undo"
            aria-label="Undo"
            @click="editorStore.undo"
          >
            <Undo2 class="h-4 w-4" />
          </button>
          <button
            type="button"
            :disabled="!editorStore.canRedo"
            class="rounded p-1.5 text-slate-600 hover:bg-slate-100 disabled:opacity-35"
            title="Redo"
            aria-label="Redo"
            @click="editorStore.redo"
          >
            <Redo2 class="h-4 w-4" />
          </button>
        </div>
      </div>

      <!-- Right: Save & Publish Actions -->
      <div class="flex items-center gap-2">
        <BaseButton
          size="sm"
          variant="outline"
          :loading="editorStore.saving"
          @click="handleSave"
        >
          <Save class="h-3.5 w-3.5" />
          <span>{{ editorStore.isDirty ? 'Save*' : 'Saved' }}</span>
        </BaseButton>

        <BaseButton
          size="sm"
          variant="primary"
          @click="showPublishModal = true"
        >
          <Globe class="h-3.5 w-3.5" />
          <span>Publish</span>
        </BaseButton>
      </div>
    </header>

    <!-- Main Editor Split Workspace -->
    <div v-if="editorStore.content && editorStore.theme" class="flex flex-1 overflow-hidden">
      <!-- Left Inspector Sidebar -->
      <aside class="flex w-80 shrink-0 flex-col border-r border-brand-border bg-white sm:w-96">
        <!-- Inspector Navigation Tabs -->
        <div class="grid grid-cols-4 border-b border-brand-border bg-slate-50/70 p-1.5 text-xs font-semibold">
          <button
            type="button"
            class="flex items-center justify-center gap-1.5 rounded-lg py-2 transition"
            :class="activeTab === 'sections' ? 'bg-white text-brand-primary shadow-2xs' : 'text-slate-600'"
            @click="activeTab = 'sections'"
          >
            <Layers class="h-3.5 w-3.5" />
            <span>Sections</span>
          </button>
          <button
            type="button"
            class="flex items-center justify-center gap-1.5 rounded-lg py-2 transition"
            :class="activeTab === 'theme' ? 'bg-white text-brand-primary shadow-2xs' : 'text-slate-600'"
            @click="activeTab = 'theme'"
          >
            <Palette class="h-3.5 w-3.5" />
            <span>Theme</span>
          </button>
          <button
            type="button"
            class="flex items-center justify-center gap-1.5 rounded-lg py-2 transition"
            :class="activeTab === 'media' ? 'bg-white text-brand-primary shadow-2xs' : 'text-slate-600'"
            @click="activeTab = 'media'"
          >
            <ImageIcon class="h-3.5 w-3.5" />
            <span>Media</span>
          </button>
          <button
            type="button"
            class="flex items-center justify-center gap-1.5 rounded-lg py-2 transition"
            :class="activeTab === 'seo' ? 'bg-white text-brand-primary shadow-2xs' : 'text-slate-600'"
            @click="activeTab = 'seo'"
          >
            <Search class="h-3.5 w-3.5" />
            <span>SEO</span>
          </button>
        </div>

        <!-- Inspector Scrollable Content -->
        <div class="flex-1 overflow-y-auto p-4">
          <!-- TAB 1: SECTIONS ORDER & CONTENT EDITOR -->
          <div v-if="activeTab === 'sections'" class="space-y-6">
            <div>
              <p class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">
                Section Order & Visibility
              </p>
              <div class="space-y-1.5">
                <div
                  v-for="(secKey, idx) in editorStore.content.sections_order"
                  :key="secKey"
                  class="flex items-center justify-between rounded-lg border px-3 py-2 text-xs transition"
                  :class="
                    editorStore.activeSection === secKey
                      ? 'border-brand-primary bg-indigo-50/70 font-semibold text-brand-primary'
                      : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'
                  "
                >
                  <button
                    type="button"
                    class="flex-1 text-left"
                    @click="editorStore.activeSection = secKey"
                  >
                    {{ sectionLabels[secKey] || secKey }}
                  </button>

                  <div class="flex items-center gap-1">
                    <button
                      type="button"
                      :disabled="idx === 0"
                      class="rounded p-1 text-slate-400 hover:bg-slate-200/60 hover:text-slate-700 disabled:opacity-25"
                      title="Move section up"
                      @click="editorStore.moveSection(secKey, 'up')"
                    >
                      <ArrowUp class="h-3.5 w-3.5" />
                    </button>
                    <button
                      type="button"
                      :disabled="idx === editorStore.content.sections_order.length - 1"
                      class="rounded p-1 text-slate-400 hover:bg-slate-200/60 hover:text-slate-700 disabled:opacity-25"
                      title="Move section down"
                      @click="editorStore.moveSection(secKey, 'down')"
                    >
                      <ArrowDown class="h-3.5 w-3.5" />
                    </button>
                    <button
                      type="button"
                      class="rounded p-1 text-slate-500 hover:bg-slate-200/60"
                      :title="editorStore.content.sections[secKey].visible ? 'Hide section' : 'Show section'"
                      @click="editorStore.toggleSectionVisibility(secKey)"
                    >
                      <Eye v-if="editorStore.content.sections[secKey].visible" class="h-3.5 w-3.5 text-brand-primary" />
                      <EyeOff v-else class="h-3.5 w-3.5 text-slate-400" />
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Active Section Fields -->
            <div class="border-t border-slate-200 pt-5">
              <h3 class="mb-4 text-xs font-bold uppercase tracking-wider text-brand-primary">
                Editing: {{ sectionLabels[editorStore.activeSection] }}
              </h3>

              <!-- HERO EDITOR -->
              <div v-if="editorStore.activeSection === 'hero'" class="space-y-4">
                <BaseInput
                  v-model="editorStore.content.sections.hero.badge"
                  label="Top Badge"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <BaseInput
                  v-model="editorStore.content.sections.hero.headline"
                  label="Hero Headline"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <BaseTextarea
                  v-model="editorStore.content.sections.hero.subheadline"
                  label="Hero Description"
                  :rows="3"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <div class="grid grid-cols-2 gap-3">
                  <BaseInput
                    v-model="editorStore.content.sections.hero.primary_cta_text"
                    label="Primary CTA Text"
                    @update:model-value="editorStore.recordSnapshot()"
                  />
                  <BaseInput
                    v-model="editorStore.content.sections.hero.primary_cta_url"
                    label="Primary CTA URL"
                    @update:model-value="editorStore.recordSnapshot()"
                  />
                </div>
                <div class="grid grid-cols-2 gap-3">
                  <BaseInput
                    v-model="editorStore.content.sections.hero.secondary_cta_text"
                    label="Secondary CTA"
                    @update:model-value="editorStore.recordSnapshot()"
                  />
                  <BaseInput
                    v-model="editorStore.content.sections.hero.secondary_cta_url"
                    label="Secondary URL"
                    @update:model-value="editorStore.recordSnapshot()"
                  />
                </div>
                <BaseInput
                  v-model="editorStore.content.sections.hero.image_url"
                  label="Hero Image URL (or upload in Media tab)"
                  placeholder="https://..."
                  @update:model-value="editorStore.recordSnapshot()"
                />
              </div>

              <!-- PROBLEM & SOLUTION EDITOR -->
              <div v-else-if="editorStore.activeSection === 'problem_solution'" class="space-y-4">
                <BaseInput
                  v-model="editorStore.content.sections.problem_solution.title"
                  label="Section Title"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <BaseTextarea
                  v-model="editorStore.content.sections.problem_solution.subtitle"
                  label="Subtitle"
                  :rows="2"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <BaseInput
                  v-model="editorStore.content.sections.problem_solution.problem_title"
                  label="Problem Column Heading"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <BaseInput
                  v-model="editorStore.content.sections.problem_solution.solution_title"
                  label="Solution Column Heading"
                  @update:model-value="editorStore.recordSnapshot()"
                />
              </div>

              <!-- FEATURES EDITOR -->
              <div v-else-if="editorStore.activeSection === 'features'" class="space-y-4">
                <BaseInput
                  v-model="editorStore.content.sections.features.title"
                  label="Features Heading"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <BaseTextarea
                  v-model="editorStore.content.sections.features.subtitle"
                  label="Features Subtitle"
                  :rows="2"
                  @update:model-value="editorStore.recordSnapshot()"
                />

                <div class="space-y-3">
                  <div
                    v-for="(item, fIdx) in editorStore.content.sections.features.items"
                    :key="fIdx"
                    class="rounded-xl border border-slate-200 bg-slate-50/70 p-3 space-y-2"
                  >
                    <div class="flex items-center justify-between">
                      <span class="text-xs font-bold text-slate-600">Feature #{{ fIdx + 1 }}</span>
                      <button
                        type="button"
                        class="text-slate-400 hover:text-brand-error"
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
                      label="Description"
                      :rows="2"
                      @update:model-value="editorStore.recordSnapshot()"
                    />
                  </div>
                </div>
                <BaseButton size="sm" variant="outline" full-width @click="addFeatureItem">
                  <Plus class="h-3.5 w-3.5" />
                  <span>Add Feature Card</span>
                </BaseButton>
              </div>

              <!-- BENEFITS EDITOR -->
              <div v-else-if="editorStore.activeSection === 'benefits'" class="space-y-4">
                <BaseInput
                  v-model="editorStore.content.sections.benefits.title"
                  label="Benefits Title"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <BaseTextarea
                  v-model="editorStore.content.sections.benefits.subtitle"
                  label="Benefits Subtitle"
                  :rows="2"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <div
                  v-for="(bItem, bIdx) in editorStore.content.sections.benefits.items"
                  :key="bIdx"
                  class="rounded-xl border border-slate-200 bg-slate-50 p-3 space-y-2"
                >
                  <BaseInput
                    v-model="bItem.metric_label"
                    label="Badge Label"
                    @update:model-value="editorStore.recordSnapshot()"
                  />
                  <BaseInput
                    v-model="bItem.title"
                    label="Benefit Headline"
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

              <!-- SOCIAL PROOF EDITOR -->
              <div v-else-if="editorStore.activeSection === 'social_proof'" class="space-y-4">
                <BaseInput
                  v-model="editorStore.content.sections.social_proof.title"
                  label="Social Proof Heading"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <div
                  v-for="(_, lIdx) in editorStore.content.sections.social_proof.logos"
                  :key="lIdx"
                >
                  <BaseInput
                    v-model="editorStore.content.sections.social_proof.logos[lIdx]"
                    :label="`Partner / Logo Name #${lIdx + 1}`"
                    @update:model-value="editorStore.recordSnapshot()"
                  />
                </div>
              </div>

              <!-- TESTIMONIALS EDITOR -->
              <div v-else-if="editorStore.activeSection === 'testimonials'" class="space-y-4">
                <BaseInput
                  v-model="editorStore.content.sections.testimonials.title"
                  label="Testimonials Heading"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <div
                  v-for="(tItem, tIdx) in editorStore.content.sections.testimonials.items"
                  :key="tIdx"
                  class="rounded-xl border border-slate-200 bg-slate-50 p-3 space-y-2"
                >
                  <BaseTextarea
                    v-model="tItem.quote"
                    label="Quote"
                    :rows="2"
                    @update:model-value="editorStore.recordSnapshot()"
                  />
                  <BaseInput
                    v-model="tItem.author_name"
                    label="Author Name"
                    @update:model-value="editorStore.recordSnapshot()"
                  />
                  <BaseInput
                    v-model="tItem.company"
                    label="Company"
                    @update:model-value="editorStore.recordSnapshot()"
                  />
                </div>
              </div>

              <!-- PRICING EDITOR -->
              <div v-else-if="editorStore.activeSection === 'pricing'" class="space-y-4">
                <BaseInput
                  v-model="editorStore.content.sections.pricing.title"
                  label="Pricing Title"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <div
                  v-for="(plan, pIdx) in editorStore.content.sections.pricing.plans"
                  :key="pIdx"
                  class="rounded-xl border border-slate-200 bg-slate-50 p-3 space-y-2"
                >
                  <BaseInput
                    v-model="plan.name"
                    label="Plan Name"
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
                      label="Period"
                      @update:model-value="editorStore.recordSnapshot()"
                    />
                  </div>
                  <BaseInput
                    v-model="plan.cta_text"
                    label="Button Text"
                    @update:model-value="editorStore.recordSnapshot()"
                  />
                </div>
              </div>

              <!-- FAQ EDITOR -->
              <div v-else-if="editorStore.activeSection === 'faq'" class="space-y-4">
                <BaseInput
                  v-model="editorStore.content.sections.faq.title"
                  label="FAQ Heading"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <div
                  v-for="(faq, qIdx) in editorStore.content.sections.faq.items"
                  :key="qIdx"
                  class="rounded-xl border border-slate-200 bg-slate-50 p-3 space-y-2"
                >
                  <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-600">Question #{{ qIdx + 1 }}</span>
                    <button
                      type="button"
                      class="text-slate-400 hover:text-brand-error"
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
                  <span>Add FAQ Item</span>
                </BaseButton>
              </div>

              <!-- CTA EDITOR -->
              <div v-else-if="editorStore.activeSection === 'cta'" class="space-y-4">
                <BaseInput
                  v-model="editorStore.content.sections.cta.headline"
                  label="CTA Headline"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <BaseTextarea
                  v-model="editorStore.content.sections.cta.subheadline"
                  label="CTA Subheadline"
                  :rows="2"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <BaseInput
                  v-model="editorStore.content.sections.cta.button_text"
                  label="Button Text"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <BaseInput
                  v-model="editorStore.content.sections.cta.button_url"
                  label="Button URL"
                  @update:model-value="editorStore.recordSnapshot()"
                />
              </div>

              <!-- FOOTER EDITOR -->
              <div v-else-if="editorStore.activeSection === 'footer'" class="space-y-4">
                <BaseInput
                  v-model="editorStore.content.sections.footer.brand_name"
                  label="Brand Name"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <BaseInput
                  v-model="editorStore.content.sections.footer.tagline"
                  label="Footer Tagline"
                  @update:model-value="editorStore.recordSnapshot()"
                />
                <BaseInput
                  v-model="editorStore.content.sections.footer.copyright_text"
                  label="Copyright Text"
                  @update:model-value="editorStore.recordSnapshot()"
                />
              </div>
            </div>
          </div>

          <!-- TAB 2: THEME COLORS & TYPOGRAPHY -->
          <div v-else-if="activeTab === 'theme'" class="space-y-5">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
              Color Tokens
            </p>
            <div
              v-for="(hex, colorKey) in editorStore.theme.colors"
              :key="colorKey"
              class="flex items-center justify-between rounded-xl border border-slate-200 p-3"
            >
              <div>
                <p class="text-xs font-bold capitalize text-slate-800">{{ colorKey }}</p>
                <p class="text-[11px] font-mono text-slate-500">{{ hex }}</p>
              </div>
              <input
                type="color"
                :value="hex"
                :aria-label="`Choose ${colorKey} color`"
                class="h-9 w-12 cursor-pointer rounded border border-slate-200 bg-white p-0.5"
                @input="editorStore.updateColor(colorKey, ($event.target as HTMLInputElement).value)"
              />
            </div>

            <div class="border-t border-slate-200 pt-4 space-y-3">
              <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                Typography Configuration
              </p>
              <div>
                <label class="mb-1 block text-xs font-semibold text-slate-700">Font Family</label>
                <select
                  :value="editorStore.theme.typography.body_font"
                  class="w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-xs"
                  @change="
                    editorStore.updateTypography(
                      'body_font',
                      ($event.target as HTMLSelectElement).value
                    )
                  "
                >
                  <option value="Inter">Inter (Modern Sans)</option>
                  <option value="system-ui">System UI Sans</option>
                  <option value="Georgia, serif">Georgia (Editorial Serif)</option>
                </select>
              </div>
            </div>
          </div>

          <!-- TAB 3: SECURE IMAGE UPLOADS -->
          <div v-else-if="activeTab === 'media'" class="space-y-5">
            <div class="rounded-xl border border-dashed border-brand-primary/50 bg-indigo-50/40 p-4 text-center">
              <Upload class="mx-auto h-6 w-6 text-brand-primary" />
              <p class="mt-2 text-xs font-bold text-brand-text">Upload Hero or Section Image</p>
              <p class="mt-0.5 text-[11px] text-brand-muted">
                Validated JPG, PNG, or WEBP up to 2MB
              </p>
              <label
                class="mt-3 inline-flex cursor-pointer items-center gap-1.5 rounded-lg bg-brand-primary px-3.5 py-2 text-xs font-semibold text-white hover:bg-brand-primary-hover"
              >
                <span>{{ uploadingMedia ? 'Uploading...' : 'Select Image File' }}</span>
                <input
                  type="file"
                  accept="image/jpeg,image/png,image/webp"
                  class="hidden"
                  :disabled="uploadingMedia"
                  @change="handleImageUpload"
                />
              </label>
              <p v-if="mediaUploadError" role="alert" class="mt-2 text-xs text-brand-error">
                {{ mediaUploadError }}
              </p>
            </div>

            <div>
              <p class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">
                Uploaded Assets ({{ mediaAssets.length }})
              </p>
              <div v-if="mediaAssets.length === 0" class="py-6 text-center text-xs text-brand-muted">
                No uploaded images yet.
              </div>
              <div v-else class="grid grid-cols-2 gap-2.5">
                <div
                  v-for="asset in mediaAssets"
                  :key="asset.id"
                  class="group relative overflow-hidden rounded-lg border border-slate-200 bg-slate-50"
                >
                  <img
                    :src="asset.url"
                    :alt="asset.original_name"
                    class="h-24 w-full object-cover"
                  />
                  <button
                    type="button"
                    class="w-full bg-slate-900/80 py-1 text-[11px] font-semibold text-white hover:bg-brand-primary"
                    @click="selectHeroImage(asset.url)"
                  >
                    Use in Hero
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- TAB 4: SEO METADATA -->
          <div v-else-if="activeTab === 'seo'" class="space-y-4">
            <BaseInput
              id="seo-title"
              v-model="editorStore.seoTitle"
              label="Page Meta Title"
              placeholder="Product Name — Value Proposition"
              @update:model-value="editorStore.recordSnapshot()"
            />
            <BaseTextarea
              id="seo-description"
              v-model="editorStore.seoDescription"
              label="Page Meta Description"
              :rows="4"
              placeholder="150–160 character summary for search engines and social cards"
              @update:model-value="editorStore.recordSnapshot()"
            />
          </div>
        </div>
      </aside>

      <!-- Right Live Preview Canvas -->
      <main class="flex-1 overflow-y-auto bg-slate-200/70 p-4 sm:p-6">
        <div
          class="mx-auto overflow-hidden rounded-2xl border border-slate-300 bg-white shadow-elevated transition-all duration-200"
          :class="{
            'w-full': editorStore.viewport === 'desktop',
            'max-w-[768px]': editorStore.viewport === 'tablet',
            'max-w-[375px]': editorStore.viewport === 'mobile',
          }"
        >
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

    <!-- Publish / Unpublish Modal -->
    <BaseModal
      :open="showPublishModal"
      title="Publish & Share Your Landing Page"
      max-width="lg"
      @close="showPublishModal = false"
    >
      <div v-if="projectStore.currentProject" class="space-y-4">
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">
              Current Status
            </span>
            <BaseBadge :variant="projectStore.currentProject.status">
              {{ projectStore.currentProject.status }}
            </BaseBadge>
          </div>
          <p class="mt-2 text-xs text-slate-600">
            Published pages are publicly viewable at their unique slug. Draft pages remain private to
            your account.
          </p>
        </div>

        <BaseInput
          id="publish-slug"
          v-model="publishSlug"
          label="Public URL Slug"
          placeholder="my-product-launch"
          hint="Lowercase letters, numbers, and hyphens only. Will be served at /p/{slug}"
        />

        <div
          v-if="projectStore.currentProject.status === 'published'"
          class="flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs text-emerald-900"
        >
          <span>Live Public URL: <strong>/p/{{ projectStore.currentProject.slug }}</strong></span>
          <RouterLink
            :to="`/p/${projectStore.currentProject.slug}`"
            target="_blank"
            class="inline-flex items-center gap-1 font-bold text-emerald-700 hover:underline"
          >
            <span>Open Live Page</span>
            <ExternalLink class="h-3.5 w-3.5" />
          </RouterLink>
        </div>

        <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-4">
          <BaseButton
            v-if="projectStore.currentProject.status === 'published'"
            variant="danger"
            :loading="publishing"
            @click="handlePublishAction(false)"
          >
            Unpublish (Revert to Draft)
          </BaseButton>
          <div v-else />

          <div class="flex items-center gap-2">
            <BaseButton variant="outline" @click="showPublishModal = false">Close</BaseButton>
            <BaseButton
              variant="primary"
              :loading="publishing"
              @click="handlePublishAction(true)"
            >
              <Globe class="h-4 w-4" />
              <span>
                {{
                  projectStore.currentProject.status === 'published'
                    ? 'Update Published Page'
                    : 'Publish Live Now'
                }}
              </span>
            </BaseButton>
          </div>
        </div>
      </div>
    </BaseModal>
  </div>
</template>
