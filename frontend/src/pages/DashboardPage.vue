<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useProjectStore } from '@/stores/projects';
import { extractErrorMessage } from '@/services/api';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import BaseBadge from '@/components/ui/BaseBadge.vue';
import { Plus, ArrowUpRight, RefreshCw } from 'lucide-vue-next';

const router = useRouter();
const authStore = useAuthStore();
const projectStore = useProjectStore();
const errorMessage = ref('');

async function loadDashboard(): Promise<void> {
  errorMessage.value = '';
  try {
    await projectStore.fetchDashboard();
  } catch (err) {
    errorMessage.value = extractErrorMessage(err, 'Failed to load workspace overview.');
  }
}

onMounted(() => {
  loadDashboard();
});
</script>

<template>
  <DashboardLayout>
    <!-- Compact Page Header -->
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
      <div>
        <div class="flex items-center gap-2">
          <h2 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-zinc-100">
            Overview
          </h2>
          <BaseBadge variant="neutral">
            engine:{{ projectStore.dashboardStats?.ai_provider || 'mock' }}
          </BaseBadge>
        </div>
        <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
          Workspace telemetry and recent builds for {{ authStore.user?.name }}.
        </p>
      </div>

      <div class="flex items-center gap-2">
        <BaseButton size="sm" variant="outline" @click="loadDashboard">
          <RefreshCw class="h-3 w-3" />
          <span>Refresh</span>
        </BaseButton>
        <RouterLink to="/templates">
          <BaseButton size="sm" variant="outline">Layouts</BaseButton>
        </RouterLink>
        <RouterLink to="/generator">
          <BaseButton size="sm" variant="primary">
            <Plus class="h-3.5 w-3.5" />
            <span>New Brief</span>
          </BaseButton>
        </RouterLink>
      </div>
    </div>

    <!-- Error Banner -->
    <div
      v-if="errorMessage"
      role="alert"
      class="mb-6 flex items-center justify-between rounded-lg border border-rose-200 bg-rose-50 px-4 py-2.5 text-xs text-rose-700 dark:border-rose-800 dark:bg-rose-950/60 dark:text-rose-300"
    >
      <span>{{ errorMessage }}</span>
      <BaseButton size="sm" variant="outline" @click="loadDashboard">Retry</BaseButton>
    </div>

    <!-- Unified 4-Column Hairline Telemetry Strip -->
    <div
      v-if="projectStore.loading && !projectStore.dashboardStats"
      class="grid grid-cols-2 divide-x divide-y divide-zinc-200 overflow-hidden rounded-lg border border-zinc-200 bg-white dark:divide-zinc-800 dark:border-zinc-800 dark:bg-zinc-900 sm:grid-cols-4 sm:divide-y-0"
    >
      <div v-for="n in 4" :key="n" class="h-24 animate-pulse p-4" />
    </div>

    <div
      v-else-if="projectStore.dashboardStats"
      class="grid grid-cols-2 divide-x divide-y divide-zinc-200 overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-subtle dark:divide-zinc-800 dark:border-zinc-800 dark:bg-zinc-900 sm:grid-cols-4 sm:divide-y-0"
    >
      <div class="p-4 sm:p-5">
        <div class="flex items-center justify-between">
          <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Total Projects</span>
          <span class="font-mono text-[10px] text-zinc-400 dark:text-zinc-500">ALL</span>
        </div>
        <p class="mt-2 font-mono text-2xl font-semibold tracking-tight text-zinc-900 dark:text-zinc-100 tabular-nums">
          {{ projectStore.dashboardStats.total_projects }}
        </p>
        <p class="mt-1 font-mono text-[11px] text-zinc-500 dark:text-zinc-400">
          {{ projectStore.dashboardStats.draft_projects }} in draft state
        </p>
      </div>

      <div class="p-4 sm:p-5">
        <div class="flex items-center justify-between">
          <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Published Live</span>
          <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" />
        </div>
        <p class="mt-2 font-mono text-2xl font-semibold tracking-tight text-zinc-900 dark:text-zinc-100 tabular-nums">
          {{ projectStore.dashboardStats.published_pages }}
        </p>
        <p class="mt-1 font-mono text-[11px] text-zinc-500 dark:text-zinc-400">
          Active public endpoints
        </p>
      </div>

      <div class="p-4 sm:p-5">
        <div class="flex items-center justify-between">
          <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Public Views</span>
          <span class="font-mono text-[10px] text-zinc-400 dark:text-zinc-500">HITS</span>
        </div>
        <p class="mt-2 font-mono text-2xl font-semibold tracking-tight text-zinc-900 dark:text-zinc-100 tabular-nums">
          {{ projectStore.dashboardStats.total_public_views }}
        </p>
        <p class="mt-1 font-mono text-[11px] text-zinc-500 dark:text-zinc-400">
          Cumulative `/p/slug` impressions
        </p>
      </div>

      <div class="p-4 sm:p-5">
        <div class="flex items-center justify-between">
          <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Copy Architect Quota</span>
          <span class="font-mono text-[10px] text-zinc-400 dark:text-zinc-500">1H WINDOW</span>
        </div>
        <p class="mt-2 font-mono text-2xl font-semibold tracking-tight text-zinc-900 dark:text-zinc-100 tabular-nums">
          {{ projectStore.dashboardStats.ai_generations_last_hour }}
          <span class="text-sm font-normal text-zinc-400 dark:text-zinc-500">
            / {{ projectStore.dashboardStats.ai_hourly_limit }}
          </span>
        </p>
        <p class="mt-1 font-mono text-[11px] text-zinc-500 dark:text-zinc-400">
          {{ projectStore.dashboardStats.ai_generations_used }} total lifetime runs
        </p>
      </div>
    </div>

    <!-- Split Workspace: Recent Projects Table (Left 8 cols) + Audit Stream (Right 4 cols) -->
    <div class="mt-6 grid gap-6 lg:grid-cols-12">
      <!-- Recent Projects Table Card -->
      <div class="lg:col-span-8">
        <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-subtle dark:border-zinc-800 dark:bg-zinc-900">
          <div class="flex items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-zinc-800">
            <div class="flex items-center gap-2">
              <h3 class="text-[13px] font-semibold text-zinc-900 dark:text-zinc-100">Recent Builds</h3>
              <span class="rounded bg-zinc-100 px-1.5 py-0.5 font-mono text-[10px] text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                {{ projectStore.recentProjects.length }}
              </span>
            </div>
            <RouterLink
              to="/projects"
              class="text-xs font-medium text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
            >
              View all →
            </RouterLink>
          </div>

          <!-- Empty State -->
          <div
            v-if="!projectStore.loading && projectStore.recentProjects.length === 0"
            class="px-6 py-12 text-center"
          >
            <p class="font-mono text-[11px] uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
              NO PROJECTS FOUND
            </p>
            <h4 class="mt-1.5 text-sm font-semibold text-zinc-900 dark:text-zinc-100">
              Your workspace has no landing pages yet
            </h4>
            <p class="mx-auto mt-1 max-w-xs text-xs text-zinc-500 dark:text-zinc-400">
              Generate a 10-section page from a product brief or pick a starter layout system.
            </p>
            <div class="mt-4 flex justify-center gap-2">
              <RouterLink to="/generator">
                <BaseButton size="sm" variant="primary">Create Page Brief</BaseButton>
              </RouterLink>
              <RouterLink to="/templates">
                <BaseButton size="sm" variant="outline">Browse Layouts</BaseButton>
              </RouterLink>
            </div>
          </div>

          <!-- High-Density Table -->
          <div v-else class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
              <thead>
                <tr class="border-b border-zinc-100 bg-zinc-50/60 font-mono text-[10px] font-medium uppercase tracking-wider text-zinc-500 dark:border-zinc-800 dark:bg-zinc-950/60 dark:text-zinc-400">
                  <th class="px-4 py-2">Project</th>
                  <th class="px-4 py-2">Endpoint</th>
                  <th class="px-4 py-2">Status</th>
                  <th class="px-4 py-2 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                <tr
                  v-for="project in projectStore.recentProjects"
                  :key="project.id"
                  class="group transition-colors hover:bg-zinc-50/80 dark:hover:bg-zinc-800/50"
                >
                  <td class="px-4 py-2.5">
                    <RouterLink
                      :to="`/projects/${project.id}/edit`"
                      class="font-medium text-zinc-900 hover:underline dark:text-zinc-100"
                    >
                      {{ project.name }}
                    </RouterLink>
                    <p v-if="project.template" class="text-[11px] text-zinc-500 dark:text-zinc-400">
                      {{ project.template.name }}
                    </p>
                  </td>
                  <td class="px-4 py-2.5 font-mono text-xs text-zinc-500 dark:text-zinc-400">
                    /p/{{ project.slug }}
                  </td>
                  <td class="px-4 py-2.5">
                    <BaseBadge :variant="project.status">{{ project.status }}</BaseBadge>
                  </td>
                  <td class="px-4 py-2.5 text-right">
                    <div class="inline-flex items-center gap-1.5">
                      <RouterLink
                        v-if="project.status === 'published'"
                        :to="`/p/${project.slug}`"
                        target="_blank"
                        class="inline-flex h-7 items-center gap-1 rounded px-2 text-xs text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white"
                      >
                        <span>Live</span>
                        <ArrowUpRight class="h-3 w-3" />
                      </RouterLink>
                      <BaseButton
                        size="sm"
                        variant="outline"
                        @click="router.push(`/projects/${project.id}/edit`)"
                      >
                        Studio
                      </BaseButton>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Right Column: Audit Stream -->
      <div class="lg:col-span-4">
        <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-subtle dark:border-zinc-800 dark:bg-zinc-900">
          <div class="flex items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-zinc-800">
            <h3 class="text-[13px] font-semibold text-zinc-900 dark:text-zinc-100">Activity Stream</h3>
            <span class="font-mono text-[10px] text-zinc-400 dark:text-zinc-500">AUDIT</span>
          </div>

          <div
            v-if="projectStore.recentActivity.length === 0"
            class="px-4 py-8 text-center font-mono text-xs text-zinc-400 dark:text-zinc-500"
          >
            No events recorded yet.
          </div>

          <ul v-else class="divide-y divide-zinc-100 px-4 py-1 dark:divide-zinc-800">
            <li
              v-for="item in projectStore.recentActivity"
              :key="item.id"
              class="py-2.5"
            >
              <div class="flex items-baseline justify-between gap-2">
                <p class="text-xs font-medium text-zinc-800 dark:text-zinc-200">{{ item.description }}</p>
              </div>
              <div class="mt-0.5 flex items-center gap-2 font-mono text-[10px] text-zinc-500 dark:text-zinc-400">
                <span>{{ item.action }}</span>
                <span>•</span>
                <span>{{ new Date(item.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</span>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>
