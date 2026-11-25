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
  Copy,
  Trash2,
  ArrowUpRight,
  LayoutGrid,
  List,
} from 'lucide-vue-next';

const router = useRouter();
const projectStore = useProjectStore();
const toastStore = useToastStore();

const viewMode = ref<'table' | 'grid'>('table');

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
    <!-- Header Bar -->
    <div class="mb-5 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
      <div>
        <h2 class="text-lg font-semibold tracking-tight text-zinc-900">Projects</h2>
        <p class="mt-0.5 text-xs text-zinc-500">
          Manage, duplicate, and publish your conversion landing pages.
        </p>
      </div>
      <div class="flex items-center gap-2">
        <RouterLink to="/generator">
          <BaseButton size="sm" variant="outline">
            Copy Architect
          </BaseButton>
        </RouterLink>
        <BaseButton size="sm" variant="primary" @click="showCreateModal = true">
          <Plus class="h-3.5 w-3.5" />
          <span>New Project</span>
        </BaseButton>
      </div>
    </div>

    <!-- Compact Linear-Style Filter Toolbar -->
    <div class="mb-4 flex flex-wrap items-center justify-between gap-2 rounded-lg border border-zinc-200 bg-white p-2 shadow-subtle">
      <div class="flex flex-1 flex-wrap items-center gap-2">
        <div class="relative min-w-[220px] flex-1 sm:max-w-xs">
          <Search class="pointer-events-none absolute left-2.5 top-2 h-3.5 w-3.5 text-zinc-400" />
          <input
            v-model="filters.search"
            type="search"
            placeholder="Filter by name or slug..."
            aria-label="Filter projects"
            class="h-7 w-full rounded border border-zinc-200 bg-zinc-50/50 pl-8 pr-3 text-xs text-zinc-900 placeholder-zinc-400 focus:border-zinc-900 focus:bg-white focus:outline-none"
            @input="loadProjects"
          />
        </div>

        <select
          v-model="filters.status"
          aria-label="Filter by status"
          class="h-7 rounded border border-zinc-200 bg-white px-2.5 text-xs text-zinc-700 focus:border-zinc-900 focus:outline-none"
          @change="loadProjects"
        >
          <option value="">Status: All</option>
          <option value="draft">Draft</option>
          <option value="published">Published</option>
          <option value="archived">Archived</option>
        </select>

        <select
          v-model="filters.sort"
          aria-label="Sort projects"
          class="h-7 rounded border border-zinc-200 bg-white px-2.5 text-xs text-zinc-700 focus:border-zinc-900 focus:outline-none"
          @change="loadProjects"
        >
          <option value="newest">Sort: Newest</option>
          <option value="updated">Sort: Recently Updated</option>
          <option value="oldest">Sort: Oldest</option>
          <option value="name">Sort: Name (A–Z)</option>
        </select>
      </div>

      <!-- Table / Grid View Toggle -->
      <div class="flex items-center rounded border border-zinc-200 bg-zinc-50 p-0.5">
        <button
          type="button"
          class="rounded p-1 transition"
          :class="viewMode === 'table' ? 'bg-white text-zinc-900 shadow-2xs' : 'text-zinc-400 hover:text-zinc-700'"
          title="Table View"
          @click="viewMode = 'table'"
        >
          <List class="h-3.5 w-3.5" />
        </button>
        <button
          type="button"
          class="rounded p-1 transition"
          :class="viewMode === 'grid' ? 'bg-white text-zinc-900 shadow-2xs' : 'text-zinc-400 hover:text-zinc-700'"
          title="Grid View"
          @click="viewMode = 'grid'"
        >
          <LayoutGrid class="h-3.5 w-3.5" />
        </button>
      </div>
    </div>

    <!-- Loading Skeleton -->
    <div v-if="projectStore.loading" class="space-y-2">
      <div
        v-for="n in 4"
        :key="n"
        class="h-12 animate-pulse rounded-lg border border-zinc-200 bg-white"
      />
    </div>

    <!-- Empty State -->
    <div
      v-else-if="projectStore.projects.length === 0"
      class="rounded-lg border border-dashed border-zinc-300 bg-white py-14 text-center"
    >
      <p class="font-mono text-[11px] uppercase tracking-wider text-zinc-400">NO MATCHING BUILDS</p>
      <h3 class="mt-1.5 text-sm font-semibold text-zinc-900">No projects match your filter</h3>
      <p class="mx-auto mt-1 max-w-sm text-xs text-zinc-500">
        Create a new project from a starter layout or generate one with Copy Architect.
      </p>
      <div class="mt-4 flex justify-center gap-2">
        <RouterLink to="/generator">
          <BaseButton size="sm" variant="primary">Open Copy Architect</BaseButton>
        </RouterLink>
        <BaseButton size="sm" variant="outline" @click="showCreateModal = true">
          New Project
        </BaseButton>
      </div>
    </div>

    <!-- TABLE VIEW (Default — High-Density Linear/Vercel Style) -->
    <div
      v-else-if="viewMode === 'table'"
      class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-subtle"
    >
      <div class="overflow-x-auto">
        <table class="w-full text-left text-[13px]">
          <thead>
            <tr class="border-b border-zinc-200 bg-zinc-50/70 font-mono text-[10px] font-medium uppercase tracking-wider text-zinc-400">
              <th class="px-4 py-2.5">Project Name</th>
              <th class="px-4 py-2.5">Public Endpoint</th>
              <th class="px-4 py-2.5">Layout System</th>
              <th class="px-4 py-2.5">Status</th>
              <th class="px-4 py-2.5">Updated</th>
              <th class="px-4 py-2.5 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-zinc-100">
            <tr
              v-for="project in projectStore.projects"
              :key="project.id"
              class="group transition-colors hover:bg-zinc-50/90"
            >
              <td class="px-4 py-3">
                <div class="flex items-center gap-2.5">
                  <span
                    class="h-2.5 w-2.5 shrink-0 rounded-xs border border-black/15"
                    :style="{ backgroundColor: project.theme?.colors?.primary || '#09090B' }"
                  />
                  <div>
                    <RouterLink
                      :to="`/projects/${project.id}/edit`"
                      class="font-semibold text-zinc-900 hover:underline"
                    >
                      {{ project.name }}
                    </RouterLink>
                    <p class="max-w-xs truncate text-xs text-zinc-400">
                      {{ project.description || project.content?.sections?.hero?.headline }}
                    </p>
                  </div>
                </div>
              </td>
              <td class="px-4 py-3 font-mono text-xs text-zinc-500">
                /p/{{ project.slug }}
              </td>
              <td class="px-4 py-3 text-xs text-zinc-600">
                {{ project.template?.name || 'Custom Schema' }}
              </td>
              <td class="px-4 py-3">
                <BaseBadge :variant="project.status">{{ project.status }}</BaseBadge>
              </td>
              <td class="px-4 py-3 font-mono text-[11px] text-zinc-400 tabular-nums">
                {{ new Date(project.updated_at).toLocaleDateString() }}
              </td>
              <td class="px-4 py-3 text-right">
                <div class="inline-flex items-center justify-end gap-1">
                  <RouterLink
                    v-if="project.status === 'published'"
                    :to="`/p/${project.slug}`"
                    target="_blank"
                    class="inline-flex h-7 items-center gap-1 rounded px-2 text-xs text-emerald-700 hover:bg-emerald-50"
                    title="Open live page"
                  >
                    <span>Live</span>
                    <ArrowUpRight class="h-3 w-3" />
                  </RouterLink>
                  <button
                    type="button"
                    class="rounded p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-900"
                    title="Duplicate project"
                    aria-label="Duplicate project"
                    @click="handleDuplicate(project)"
                  >
                    <Copy class="h-3.5 w-3.5" />
                  </button>
                  <button
                    type="button"
                    class="rounded p-1.5 text-zinc-400 hover:bg-rose-50 hover:text-rose-600"
                    title="Delete project"
                    aria-label="Delete project"
                    @click="projectToDelete = project"
                  >
                    <Trash2 class="h-3.5 w-3.5" />
                  </button>
                  <BaseButton
                    size="sm"
                    variant="outline"
                    @click="router.push(`/projects/${project.id}/edit`)"
                  >
                    Studio →
                  </BaseButton>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- GRID VIEW -->
    <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <div
        v-for="project in projectStore.projects"
        :key="project.id"
        class="flex flex-col justify-between rounded-lg border border-zinc-200 bg-white p-5 shadow-subtle transition hover:border-zinc-300"
      >
        <div>
          <div class="mb-3 flex items-center justify-between">
            <div class="flex items-center gap-1">
              <span
                class="h-3 w-3 rounded-xs border border-black/10"
                :style="{ backgroundColor: project.theme?.colors?.primary || '#09090B' }"
              />
              <span
                class="h-3 w-3 rounded-xs border border-black/10"
                :style="{ backgroundColor: project.theme?.colors?.secondary || '#E11D48' }"
              />
            </div>
            <BaseBadge :variant="project.status">{{ project.status }}</BaseBadge>
          </div>

          <h3 class="text-sm font-semibold text-zinc-900">{{ project.name }}</h3>
          <p class="mt-1 line-clamp-2 text-xs text-zinc-500">
            {{ project.description || project.content?.sections?.hero?.headline }}
          </p>
          <p class="mt-3 font-mono text-[11px] text-zinc-400">/p/{{ project.slug }}</p>
        </div>

        <div class="mt-4 flex items-center justify-between border-t border-zinc-100 pt-3">
          <div class="flex items-center gap-1">
            <button
              type="button"
              class="rounded p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-900"
              title="Duplicate"
              @click="handleDuplicate(project)"
            >
              <Copy class="h-3.5 w-3.5" />
            </button>
            <button
              type="button"
              class="rounded p-1.5 text-zinc-400 hover:bg-rose-50 hover:text-rose-600"
              title="Delete"
              @click="projectToDelete = project"
            >
              <Trash2 class="h-3.5 w-3.5" />
            </button>
          </div>
          <BaseButton
            size="sm"
            variant="primary"
            @click="router.push(`/projects/${project.id}/edit`)"
          >
            Open Studio →
          </BaseButton>
        </div>
      </div>
    </div>

    <!-- Create Project Modal -->
    <BaseModal
      :open="showCreateModal"
      title="New Landing Page Project"
      max-width="lg"
      @close="showCreateModal = false"
    >
      <div
        v-if="createError"
        role="alert"
        class="mb-4 rounded-md border border-rose-200 bg-rose-50 p-3 text-xs text-rose-700"
      >
        {{ createError }}
      </div>
      <form class="space-y-4" @submit.prevent="handleCreateProject">
        <BaseInput
          id="new-project-name"
          v-model="createForm.name"
          label="Project Title"
          placeholder="e.g., CloudPulse FinOps Launch"
          required
        />

        <BaseTextarea
          id="new-project-desc"
          v-model="createForm.description"
          label="Internal Notes / Description"
          placeholder="Brief summary of this campaign"
          :rows="2"
        />

        <div>
          <label
            for="new-project-template"
            class="mb-1.5 block text-xs font-medium text-zinc-700"
          >
            Layout System
          </label>
          <select
            id="new-project-template"
            v-model="createForm.template_id"
            class="h-8 w-full rounded-md border border-zinc-200 bg-white px-2.5 text-[13px] text-zinc-900 focus:border-zinc-900 focus:outline-none"
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

        <div class="mt-6 flex justify-end gap-2">
          <BaseButton variant="outline" @click="showCreateModal = false">Cancel</BaseButton>
          <BaseButton type="submit" variant="primary" :loading="createSubmitting">
            Create & Open Studio
          </BaseButton>
        </div>
      </form>
    </BaseModal>

    <!-- Destructive Delete Confirmation Dialog -->
    <ConfirmDialog
      :open="projectToDelete !== null"
      title="Delete Project Permanently?"
      :message="`Are you sure you want to delete '${projectToDelete?.name}'? If published, its public endpoint (/p/${projectToDelete?.slug}) will immediately return 404.`"
      confirm-label="Delete Project"
      variant="danger"
      :loading="deleting"
      @cancel="projectToDelete = null"
      @confirm="confirmDeleteProject"
    />
  </DashboardLayout>
</template>
