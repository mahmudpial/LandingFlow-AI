<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useProjectStore } from '@/stores/projects';
import { useToastStore } from '@/stores/toast';
import { createProjectSchema } from '@/utils/validation';
import { extractErrorMessage } from '@/services/api';
import type { Project } from '@/types';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import BaseInput from '@/components/ui/BaseInput.vue';
import BaseTextarea from '@/components/ui/BaseTextarea.vue';
import BaseBadge from '@/components/ui/BaseBadge.vue';
import BaseModal from '@/components/ui/BaseModal.vue';
import ConfirmDialog from '@/components/ui/ConfirmDialog.vue';
import {
  Plus,
  Search,
  Edit3,
  Copy,
  Trash2,
  ExternalLink,
  Sparkles,
  FolderKanban,
} from 'lucide-vue-next';

const router = useRouter();
const projectStore = useProjectStore();
const toastStore = useToastStore();

const filters = reactive({
  search: '',
  status: '',
  sort: 'newest',
});

const showCreateModal = ref(false);
const createSubmitting = ref(false);
const createError = ref('');
const createForm = reactive({
  name: '',
  description: '',
  template_id: undefined as number | undefined,
});

const projectToDelete = ref<Project | null>(null);
const deleting = ref(false);

async function loadProjects(): Promise<void> {
  await projectStore.fetchProjects({
    search: filters.search || undefined,
    status: filters.status || undefined,
    sort: filters.sort,
  });
}

onMounted(async () => {
  await Promise.all([loadProjects(), projectStore.fetchTemplates()]);
  if (projectStore.templates.length > 0) {
    createForm.template_id = projectStore.templates[0].id;
  }
});

async function handleCreateProject(): Promise<void> {
  createError.value = '';
  const parsed = createProjectSchema.safeParse(createForm);
  if (!parsed.success) {
    createError.value = parsed.error.issues[0]?.message || 'Please check project details.';
    return;
  }

  createSubmitting.value = true;
  try {
    const project = await projectStore.createProject(parsed.data);
    showCreateModal.value = false;
    createForm.name = '';
    createForm.description = '';
    toastStore.success('Project created', `Opened "${project.name}".`);
    router.push(`/projects/${project.id}/edit`);
  } catch (err) {
    createError.value = extractErrorMessage(err);
  } finally {
    createSubmitting.value = false;
  }
}

async function handleDuplicate(project: Project): Promise<void> {
  try {
    const copy = await projectStore.duplicateProject(project.id);
    toastStore.success('Project duplicated', `Created "${copy.name}".`);
  } catch (err) {
    toastStore.error('Duplication failed', extractErrorMessage(err));
  }
}

async function confirmDeleteProject(): Promise<void> {
  if (!projectToDelete.value) return;
  deleting.value = true;
  try {
    const targetName = projectToDelete.value.name;
    await projectStore.deleteProject(projectToDelete.value.id);
    toastStore.success('Project deleted', `"${targetName}" has been removed.`);
    projectToDelete.value = null;
  } catch (err) {
    toastStore.error('Delete failed', extractErrorMessage(err));
  } finally {
    deleting.value = false;
  }
}
</script>

<template>
  <DashboardLayout>
    <!-- Header & Controls -->
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
      <div>
        <h2 class="text-xl font-bold text-brand-text">My Landing Page Projects</h2>
        <p class="mt-0.5 text-sm text-brand-muted">
          Manage, duplicate, edit, and publish your landing page projects.
        </p>
      </div>
      <div class="flex items-center gap-2.5">
        <RouterLink to="/generator">
          <BaseButton variant="secondary">
            <Sparkles class="h-4 w-4" />
            <span>AI Generator Wizard</span>
          </BaseButton>
        </RouterLink>
        <BaseButton variant="primary" @click="showCreateModal = true">
          <Plus class="h-4 w-4" />
          <span>Blank / Template Project</span>
        </BaseButton>
      </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="mb-6 grid gap-3 rounded-2xl border border-brand-border bg-white p-4 shadow-card sm:grid-cols-4">
      <div class="relative sm:col-span-2">
        <Search class="pointer-events-none absolute left-3.5 top-2.5 h-4 w-4 text-slate-400" />
        <input
          v-model="filters.search"
          type="search"
          placeholder="Search projects by name, description, or slug..."
          aria-label="Search projects"
          class="w-full rounded-lg border border-brand-border bg-white py-2 pl-10 pr-4 text-sm focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary/20"
          @input="loadProjects"
        />
      </div>

      <div>
        <select
          v-model="filters.status"
          aria-label="Filter by status"
          class="w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-sm text-brand-text focus:border-brand-primary focus:outline-none"
          @change="loadProjects"
        >
          <option value="">All Statuses</option>
          <option value="draft">Draft</option>
          <option value="published">Published</option>
          <option value="archived">Archived</option>
        </select>
      </div>

      <div>
        <select
          v-model="filters.sort"
          aria-label="Sort projects"
          class="w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-sm text-brand-text focus:border-brand-primary focus:outline-none"
          @change="loadProjects"
        >
          <option value="newest">Newest First</option>
          <option value="updated">Recently Updated</option>
          <option value="oldest">Oldest First</option>
          <option value="name">Name (A–Z)</option>
        </select>
      </div>
    </div>

    <!-- Loading Skeleton -->
    <div v-if="projectStore.loading" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <div
        v-for="n in 3"
        :key="n"
        class="h-56 animate-pulse rounded-2xl border border-brand-border bg-white p-6"
      />
    </div>

    <!-- Empty State -->
    <div
      v-else-if="projectStore.projects.length === 0"
      class="rounded-2xl border border-dashed border-slate-300 bg-white py-16 text-center"
    >
      <FolderKanban class="mx-auto h-12 w-12 text-slate-400" />
      <h3 class="mt-4 text-base font-bold text-brand-text">No matching projects found</h3>
      <p class="mx-auto mt-1 max-w-md text-xs text-brand-muted">
        Create a new landing page project with the AI Generator or choose a starter template.
      </p>
      <div class="mt-6 flex justify-center gap-3">
        <RouterLink to="/generator">
          <BaseButton variant="primary">
            <Sparkles class="h-4 w-4" />
            <span>Launch AI Generator</span>
          </BaseButton>
        </RouterLink>
        <BaseButton variant="outline" @click="showCreateModal = true">
          <Plus class="h-4 w-4" />
          <span>New Project</span>
        </BaseButton>
      </div>
    </div>

    <!-- Project Cards Grid -->
    <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <div
        v-for="project in projectStore.projects"
        :key="project.id"
        class="flex flex-col justify-between rounded-2xl border border-brand-border bg-white p-6 shadow-card transition hover:shadow-elevated"
      >
        <div>
          <!-- Palette Color Preview Strip -->
          <div class="mb-4 flex items-center justify-between">
            <div class="flex items-center gap-1.5">
              <span
                class="h-4 w-4 rounded-full border border-slate-200"
                :style="{ backgroundColor: project.theme?.colors?.primary || '#635BFF' }"
              />
              <span
                class="h-4 w-4 rounded-full border border-slate-200"
                :style="{ backgroundColor: project.theme?.colors?.secondary || '#8B5CF6' }"
              />
              <span
                class="h-4 w-4 rounded-full border border-slate-200"
                :style="{ backgroundColor: project.theme?.colors?.accent || '#14B8A6' }"
              />
            </div>
            <BaseBadge :variant="project.status">{{ project.status }}</BaseBadge>
          </div>

          <h3 class="text-base font-bold text-brand-text">
            {{ project.name }}
          </h3>
          <p class="mt-1 line-clamp-2 text-xs text-brand-muted">
            {{ project.description || project.content?.sections?.hero?.headline || 'Ready for editing' }}
          </p>

          <div class="mt-4 rounded-lg bg-slate-50 px-3 py-2 text-[11px] text-slate-600">
            <div class="flex items-center justify-between">
              <span class="font-medium">Public URL:</span>
              <code class="text-brand-primary">/p/{{ project.slug }}</code>
            </div>
            <div v-if="project.template" class="mt-1 flex items-center justify-between">
              <span class="font-medium">Template:</span>
              <span>{{ project.template.name }}</span>
            </div>
          </div>
        </div>

        <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-4">
          <div class="flex items-center gap-1">
            <button
              type="button"
              class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-brand-text"
              title="Duplicate project"
              aria-label="Duplicate project"
              @click="handleDuplicate(project)"
            >
              <Copy class="h-4 w-4" />
            </button>
            <button
              type="button"
              class="rounded-lg p-2 text-slate-500 hover:bg-red-50 hover:text-brand-error"
              title="Delete project"
              aria-label="Delete project"
              @click="projectToDelete = project"
            >
              <Trash2 class="h-4 w-4" />
            </button>
            <RouterLink
              v-if="project.status === 'published'"
              :to="`/p/${project.slug}`"
              target="_blank"
              class="rounded-lg p-2 text-brand-success hover:bg-emerald-50"
              title="Open published public page"
            >
              <ExternalLink class="h-4 w-4" />
            </RouterLink>
          </div>

          <BaseButton
            size="sm"
            variant="primary"
            @click="router.push(`/projects/${project.id}/edit`)"
          >
            <Edit3 class="h-3.5 w-3.5" />
            <span>Edit Page</span>
          </BaseButton>
        </div>
      </div>
    </div>

    <!-- Create Project Modal -->
    <BaseModal
      :open="showCreateModal"
      title="Create New Landing Page Project"
      max-width="lg"
      @close="showCreateModal = false"
    >
      <div
        v-if="createError"
        role="alert"
        class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-xs text-brand-error"
      >
        {{ createError }}
      </div>
      <form class="space-y-4" @submit.prevent="handleCreateProject">
        <BaseInput
          id="new-project-name"
          v-model="createForm.name"
          label="Project Name"
          placeholder="e.g., CloudPulse Analytics Launch"
          required
        />

        <BaseTextarea
          id="new-project-desc"
          v-model="createForm.description"
          label="Description (Optional)"
          placeholder="Brief overview of this landing page campaign"
          :rows="2"
        />

        <div>
          <label
            for="new-project-template"
            class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-700"
          >
            Starter Template
          </label>
          <select
            id="new-project-template"
            v-model="createForm.template_id"
            class="w-full rounded-lg border border-brand-border bg-white px-3.5 py-2 text-sm text-brand-text focus:border-brand-primary focus:outline-none"
          >
            <option
              v-for="tpl in projectStore.templates"
              :key="tpl.id"
              :value="tpl.id"
            >
              {{ tpl.name }} ({{ tpl.category.toUpperCase() }})
            </option>
          </select>
        </div>

        <div class="mt-6 flex justify-end gap-2.5">
          <BaseButton variant="outline" @click="showCreateModal = false">Cancel</BaseButton>
          <BaseButton type="submit" variant="primary" :loading="createSubmitting">
            Create & Open Editor
          </BaseButton>
        </div>
      </form>
    </BaseModal>

    <!-- Destructive Delete Confirmation Dialog -->
    <ConfirmDialog
      :open="projectToDelete !== null"
      title="Delete Project Permanently?"
      :message="`Are you sure you want to delete '${projectToDelete?.name}'? If published, its public URL (/p/${projectToDelete?.slug}) will immediately stop working.`"
      confirm-label="Delete Project"
      variant="danger"
      :loading="deleting"
      @cancel="projectToDelete = null"
      @confirm="confirmDeleteProject"
    />
  </DashboardLayout>
</template>
