<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useProjectStore } from '@/stores/projects';
import { useToastStore } from '@/stores/toast';
import { extractErrorMessage } from '@/services/api';
import type { Template } from '@/types';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import BaseModal from '@/components/ui/BaseModal.vue';
import LandingPageRenderer from '@/components/landing/LandingPageRenderer.vue';

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
      const name = newProjectName.value.trim() || `${template.name} Build`;
      const created = await projectStore.createProject({
        name,
        description: `Initialized from ${template.name}`,
        template_id: template.id,
      });
      toastStore.success('Project initialized', `Opened "${created.name}".`);
      router.push(`/projects/${created.id}/edit`);
    } else {
      const updated = await projectStore.applyTemplate(
        applyingToProjectId.value,
        template.id,
        false
      );
      toastStore.success(
        'Layout system updated',
        `Applied ${template.name} to "${updated.name}" while preserving your copy.`
      );
      router.push(`/projects/${updated.id}/edit`);
    }
  } catch (err) {
    toastStore.error('Failed to apply layout', extractErrorMessage(err));
  } finally {
    submitting.value = false;
  }
}
</script>

<template>
  <DashboardLayout>
    <!-- Header & Target Bar -->
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
      <div>
        <h2 class="text-lg font-semibold tracking-tight text-zinc-900">Layout Systems</h2>
        <p class="mt-0.5 text-xs text-zinc-500">
          Switch layout systems non-destructively on any project or initialize a new build.
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-2 rounded-lg border border-zinc-200 bg-white p-1.5 shadow-subtle">
        <span class="px-2 font-mono text-[10px] font-medium uppercase text-zinc-400">Target:</span>
        <select
          v-model="applyingToProjectId"
          aria-label="Target project for template"
          class="h-7 rounded border border-zinc-200 bg-zinc-50 px-2 text-xs font-medium text-zinc-800 focus:border-zinc-900 focus:outline-none"
        >
          <option value="new">+ Initialize New Project</option>
          <option
            v-for="proj in projectStore.projects"
            :key="proj.id"
            :value="proj.id"
          >
            Apply to: {{ proj.name }} (Keeps copy)
          </option>
        </select>
        <input
          v-if="applyingToProjectId === 'new'"
          v-model="newProjectName"
          type="text"
          placeholder="Optional project name..."
          class="h-7 rounded border border-zinc-200 px-2.5 text-xs focus:border-zinc-900 focus:outline-none"
        />
      </div>
    </div>

    <!-- Layout System Cards -->
    <div class="grid gap-5 lg:grid-cols-3">
      <div
        v-for="(template, idx) in projectStore.templates"
        :key="template.id"
        class="flex flex-col justify-between overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-subtle transition hover:border-zinc-300"
      >
        <div>
          <!-- Wireframe SVG Preview Header -->
          <div class="relative h-44 overflow-hidden border-b border-zinc-200 bg-zinc-950">
            <img
              v-if="template.preview_image_url"
              :src="template.preview_image_url"
              :alt="`${template.name} wireframe`"
              class="h-full w-full object-cover opacity-95"
            />
            <div class="absolute left-3 top-3 flex items-center gap-1.5">
              <span class="rounded bg-zinc-900/90 px-2 py-0.5 font-mono text-[10px] font-medium uppercase text-white">
                0{{ idx + 1 }} / {{ template.category }}
              </span>
            </div>
            <div class="absolute bottom-3 right-3 flex items-center gap-1 rounded-full bg-zinc-900/80 px-2 py-1 backdrop-blur-xs">
              <span
                v-for="(hex, key) in template.color_palette"
                :key="key"
                class="h-2.5 w-2.5 rounded-full border border-white/40"
                :style="{ backgroundColor: hex }"
                :title="`${key}: ${hex}`"
              />
            </div>
          </div>

          <div class="p-5">
            <div class="flex items-baseline justify-between">
              <h3 class="text-sm font-semibold text-zinc-900">
                {{ template.name }}
              </h3>
              <span class="font-mono text-[10px] text-zinc-400">
                {{ template.typography.scale }}
              </span>
            </div>

            <p class="mt-1.5 text-xs leading-relaxed text-zinc-500">
              {{ template.description }}
            </p>

            <div class="mt-4 border-t border-zinc-100 pt-3">
              <p class="font-mono text-[10px] uppercase tracking-wider text-zinc-400">
                10 Schema Blocks Included
              </p>
              <div class="mt-1.5 flex flex-wrap gap-1">
                <span
                  v-for="sec in template.supported_sections.slice(0, 6)"
                  :key="sec"
                  class="rounded bg-zinc-100 px-1.5 py-0.5 font-mono text-[10px] text-zinc-600"
                >
                  {{ sec.replace('_', ' ') }}
                </span>
                <span class="rounded bg-zinc-50 px-1.5 py-0.5 font-mono text-[10px] text-zinc-400">
                  +4 more
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-between border-t border-zinc-100 bg-zinc-50/60 px-5 py-3">
          <BaseButton
            size="sm"
            variant="outline"
            @click="previewTemplate = template"
          >
            Inspect Layout
          </BaseButton>

          <BaseButton
            size="sm"
            variant="primary"
            :loading="submitting"
            @click="handleUseTemplate(template)"
          >
            <span>
              {{ applyingToProjectId === 'new' ? 'Initialize Build →' : 'Apply Layout →' }}
            </span>
          </BaseButton>
        </div>
      </div>
    </div>

    <!-- Full Layout Preview Modal -->
    <BaseModal
      :open="previewTemplate !== null"
      :title="`Layout Inspection: ${previewTemplate?.name || ''}`"
      max-width="2xl"
      @close="previewTemplate = null"
    >
      <div v-if="previewTemplate" class="max-h-[72vh] overflow-y-auto rounded-lg border border-zinc-200">
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
          Use This Layout →
        </BaseButton>
      </div>
    </BaseModal>
  </DashboardLayout>
</template>
