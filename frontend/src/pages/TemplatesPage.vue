<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useProjectStore } from '@/stores/projects';
import { useToastStore } from '@/stores/toast';
import { extractErrorMessage } from '@/services/api';
import type { Template } from '@/types';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import BaseBadge from '@/components/ui/BaseBadge.vue';
import BaseModal from '@/components/ui/BaseModal.vue';
import LandingPageRenderer from '@/components/landing/LandingPageRenderer.vue';
import { Eye, Plus, CheckCircle2, Layers } from 'lucide-vue-next';

const router = useRouter();
const projectStore = useProjectStore();
const toastStore = useToastStore();

const previewTemplate = ref<Template | null>(null);
const applyingToProjectId = ref<number | 'new'>('new');
const newProjectName = ref('');
const submitting = ref(false);

onMounted(async () => {
  await Promise.all([projectStore.fetchTemplates(), projectStore.fetchProjects()]);
});

async function handleUseTemplate(template: Template): Promise<void> {
  submitting.value = true;
  try {
    if (applyingToProjectId.value === 'new') {
      const name = newProjectName.value.trim() || `${template.name} Project`;
      const created = await projectStore.createProject({
        name,
        description: `Created from ${template.name} template`,
        template_id: template.id,
      });
      toastStore.success('Project created from template', `Opened "${created.name}".`);
      router.push(`/projects/${created.id}/edit`);
    } else {
      const updated = await projectStore.applyTemplate(
        applyingToProjectId.value,
        template.id,
        false
      );
      toastStore.success(
        'Template applied!',
        `Updated "${updated.name}" to ${template.name} while preserving your copy.`
      );
      router.push(`/projects/${updated.id}/edit`);
    }
  } catch (err) {
    toastStore.error('Failed to apply template', extractErrorMessage(err));
  } finally {
    submitting.value = false;
  }
}
</script>

<template>
  <DashboardLayout>
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
      <div>
        <h2 class="text-xl font-bold text-brand-text">Reusable Template Library</h2>
        <p class="mt-1 text-sm text-brand-muted">
          Switch templates anytime without losing your project copy, or launch a new project from a preset.
        </p>
      </div>

      <!-- Target Selector -->
      <div class="flex flex-wrap items-center gap-2 rounded-xl border border-brand-border bg-white p-2.5 shadow-2xs">
        <span class="text-xs font-semibold text-slate-600">Apply to:</span>
        <select
          v-model="applyingToProjectId"
          aria-label="Target project for template"
          class="rounded-lg border border-brand-border bg-slate-50 px-2.5 py-1.5 text-xs font-medium text-brand-text"
        >
          <option value="new">+ New Project</option>
          <option
            v-for="proj in projectStore.projects"
            :key="proj.id"
            :value="proj.id"
          >
            Existing: {{ proj.name }} (Preserves copy)
          </option>
        </select>
        <input
          v-if="applyingToProjectId === 'new'"
          v-model="newProjectName"
          type="text"
          placeholder="Optional new project title..."
          class="rounded-lg border border-brand-border px-2.5 py-1.5 text-xs"
        />
      </div>
    </div>

    <!-- Template Cards -->
    <div class="grid gap-6 lg:grid-cols-3">
      <div
        v-for="template in projectStore.templates"
        :key="template.id"
        class="flex flex-col justify-between rounded-2xl border border-brand-border bg-white overflow-hidden shadow-card transition hover:shadow-elevated"
      >
        <div>
          <!-- SVG Template Preview Banner -->
          <div class="relative h-44 overflow-hidden bg-slate-900">
            <img
              v-if="template.preview_image_url"
              :src="template.preview_image_url"
              :alt="`${template.name} preview`"
              class="h-full w-full object-cover opacity-90 transition duration-300 hover:scale-105"
            />
            <div
              class="
                absolute inset-0 flex flex-col justify-between
                bg-gradient-to-t from-slate-950/90 via-slate-900/35 to-transparent p-5 text-white
              "
            >
              <div class="flex items-center justify-between">
                <span class="rounded-full bg-white/20 px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider backdrop-blur-xs">
                  {{ template.category }}
                </span>
                <div class="flex items-center gap-1.5 rounded-full bg-black/40 px-2.5 py-1 backdrop-blur-xs">
                  <span
                    v-for="(hex, key) in template.color_palette"
                    :key="key"
                    class="h-3 w-3 rounded-full border border-white/60"
                    :style="{ backgroundColor: hex }"
                    :title="`${key}: ${hex}`"
                  />
                </div>
              </div>
              <div>
                <h3 class="font-display text-lg font-bold tracking-tight">
                  {{ template.name }}
                </h3>
                <p class="mt-0.5 text-xs text-white/80">
                  Typography: {{ template.typography.heading_font }} • Scale: {{ template.typography.scale }}
                </p>
              </div>
            </div>
          </div>

          <div class="p-6">
            <p class="text-sm leading-relaxed text-brand-muted">
              {{ template.description }}
            </p>

            <div class="mt-4">
              <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                Supported Sections ({{ template.supported_sections.length }})
              </p>
              <div class="mt-2 flex flex-wrap gap-1.5">
                <BaseBadge
                  v-for="sec in template.supported_sections"
                  :key="sec"
                  variant="neutral"
                >
                  {{ sec.replace('_', ' ') }}
                </BaseBadge>
              </div>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-between border-t border-slate-100 bg-slate-50/50 px-6 py-4">
          <BaseButton
            size="sm"
            variant="outline"
            @click="previewTemplate = template"
          >
            <Eye class="h-3.5 w-3.5" />
            <span>Live Preview</span>
          </BaseButton>

          <BaseButton
            size="sm"
            variant="primary"
            :loading="submitting"
            @click="handleUseTemplate(template)"
          >
            <CheckCircle2 v-if="applyingToProjectId !== 'new'" class="h-3.5 w-3.5" />
            <Plus v-else class="h-3.5 w-3.5" />
            <span>
              {{ applyingToProjectId === 'new' ? 'Use Template' : 'Apply to Project' }}
            </span>
          </BaseButton>
        </div>
      </div>
    </div>

    <!-- Full Template Preview Modal -->
    <BaseModal
      :open="previewTemplate !== null"
      :title="`Template Preview: ${previewTemplate?.name || ''}`"
      max-width="2xl"
      @close="previewTemplate = null"
    >
      <div v-if="previewTemplate" class="max-h-[75vh] overflow-y-auto rounded-xl border border-brand-border">
        <LandingPageRenderer
          :content="previewTemplate.default_schema"
          :theme="{
            template_slug: previewTemplate.slug,
            layout_variant: previewTemplate.category,
            colors: previewTemplate.color_palette,
            typography: previewTemplate.typography,
          }"
        />
      </div>
      <div class="mt-4 flex justify-end gap-2">
        <BaseButton variant="outline" @click="previewTemplate = null">Close</BaseButton>
        <BaseButton
          v-if="previewTemplate"
          variant="primary"
          @click="handleUseTemplate(previewTemplate)"
        >
          <Layers class="h-4 w-4" />
          <span>Use This Template</span>
        </BaseButton>
      </div>
    </BaseModal>
  </DashboardLayout>
</template>
