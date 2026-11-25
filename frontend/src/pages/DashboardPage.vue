<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useProjectStore } from '@/stores/projects';
import { extractErrorMessage } from '@/services/api';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import BaseBadge from '@/components/ui/BaseBadge.vue';

const router = useRouter();
const authStore = useAuthStore();
const projectStore = useProjectStore();
const errorMessage = ref('');

async function loadDashboard(): Promise<void> {
  errorMessage.value = '';
  try {
    await projectStore.fetchDashboard();
  } catch (err) {
    errorMessage.value = extractErrorMessage(err, 'Failed to load workspace telemetry.');
  }
}

onMounted(() => {
  loadDashboard();
});
</script>

<template>
  <DashboardLayout>
    <!-- Studio Welcome Header -->
    <div
      class="mb-8 flex flex-col justify-between gap-4 rounded-xl border border-brand-border bg-white p-6 shadow-card sm:flex-row sm:items-center"
    >
      <div>
        <div class="flex flex-wrap items-center gap-2.5">
          <span class="font-mono text-[11px] font-semibold uppercase tracking-widest text-brand-secondary">
            Studio Overview
          </span>
          <BaseBadge variant="neutral">
            Engine: {{ projectStore.dashboardStats?.ai_provider || 'mock' }}
          </BaseBadge>
        </div>
        <h2 class="mt-1.5 text-xl font-extrabold tracking-tight text-brand-text sm:text-2xl">
          Welcome back, {{ authStore.user?.name }}
        </h2>
        <p class="mt-1 text-sm text-brand-muted">
          Monitor active landing page builds, public conversion traffic, and workspace activity.
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-2.5">
        <RouterLink to="/templates">
          <BaseButton variant="outline">Browse Layout Systems</BaseButton>
        </RouterLink>
        <RouterLink to="/generator">
          <BaseButton variant="primary">
            <span>+ Draft With Copy Architect</span>
          </BaseButton>
        </RouterLink>
      </div>
    </div>

    <!-- Error state -->
    <div
      v-if="errorMessage"
      role="alert"
      class="mb-6 flex items-center justify-between rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-brand-error"
    >
      <span>{{ errorMessage }}</span>
      <BaseButton size="sm" variant="outline" @click="loadDashboard">Retry</BaseButton>
    </div>

    <!-- Loading Skeleton -->
    <div v-if="projectStore.loading && !projectStore.dashboardStats" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
      <div
        v-for="n in 4"
        :key="n"
        class="h-32 animate-pulse rounded-xl border border-brand-border bg-white p-5"
      />
    </div>

    <!-- Live Metric Cards (Tabular Monospace Typography) -->
    <div v-else-if="projectStore.dashboardStats" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
      <div class="card-hover-lift rounded-xl border border-brand-border bg-white p-5 shadow-card animate-fade-up">
        <div class="flex items-center justify-between border-b border-brand-border pb-2.5">
          <span class="font-mono text-[11px] font-semibold uppercase tracking-wider text-brand-muted">
            Total Projects
          </span>
          <span class="font-mono text-[11px] text-brand-secondary">01</span>
        </div>
        <p class="mt-4 font-mono text-3xl font-bold tracking-tight text-brand-text">
          {{ projectStore.dashboardStats.total_projects }}
        </p>
        <p class="mt-1.5 font-mono text-xs text-brand-muted">
          {{ projectStore.dashboardStats.draft_projects }} active draft(s)
        </p>
      </div>

      <div class="card-hover-lift rounded-xl border border-brand-border bg-white p-5 shadow-card animate-fade-up stagger-1">
        <div class="flex items-center justify-between border-b border-brand-border pb-2.5">
          <span class="font-mono text-[11px] font-semibold uppercase tracking-wider text-brand-muted">
            Published Live
          </span>
          <span class="font-mono text-[11px] text-brand-success">02</span>
        </div>
        <p class="mt-4 font-mono text-3xl font-bold tracking-tight text-brand-text">
          {{ projectStore.dashboardStats.published_pages }}
        </p>
        <p class="mt-1.5 font-mono text-xs text-brand-muted">
          {{ projectStore.dashboardStats.total_public_views }} public page views
        </p>
      </div>

      <div class="card-hover-lift rounded-xl border border-brand-border bg-white p-5 shadow-card animate-fade-up stagger-2">
        <div class="flex items-center justify-between border-b border-brand-border pb-2.5">
          <span class="font-mono text-[11px] font-semibold uppercase tracking-wider text-brand-muted">
            Copy Generations
          </span>
          <span class="font-mono text-[11px] text-brand-muted">03</span>
        </div>
        <p class="mt-4 font-mono text-3xl font-bold tracking-tight text-brand-text">
          {{ projectStore.dashboardStats.ai_generations_used }}
        </p>
        <p class="mt-1.5 font-mono text-xs text-brand-muted">
          {{ projectStore.dashboardStats.ai_generations_last_hour }}/{{ projectStore.dashboardStats.ai_hourly_limit }} hourly quota
        </p>
      </div>

      <div class="flex flex-col justify-between rounded-xl border border-brand-primary bg-[#111318] p-5 text-[#FAF9F5] shadow-card animate-fade-up stagger-3">
        <div>
          <span class="font-mono text-[10px] font-semibold uppercase tracking-[0.16em] text-brand-secondary">
            Quick Launch
          </span>
          <p class="mt-1.5 text-sm font-bold text-white">
            Start a new conversion brief
          </p>
          <p class="mt-1 text-xs text-[#A1A09A]">
            Generate all 10 sections from a 3-step product brief.
          </p>
        </div>
        <RouterLink to="/generator" class="mt-4">
          <BaseButton size="sm" variant="secondary" full-width>
            Open Copy Architect →
          </BaseButton>
        </RouterLink>
      </div>
    </div>

    <!-- Main 2-Column Content: Recent Projects + Audit Log -->
    <div class="mt-8 grid gap-8 lg:grid-cols-3">
      <!-- Recent Projects (2 cols) -->
      <div class="lg:col-span-2">
        <div class="rounded-xl border border-brand-border bg-white p-6 shadow-card">
          <div class="mb-5 flex items-center justify-between border-b border-brand-border pb-3.5">
            <div>
              <h3 class="text-base font-bold text-brand-text">Recent Projects</h3>
              <p class="text-xs text-brand-muted">Latest landing pages modified in your workspace</p>
            </div>
            <RouterLink to="/projects" class="font-mono text-xs font-semibold text-brand-text underline decoration-brand-secondary underline-offset-4 hover:text-brand-secondary">
              All Projects →
            </RouterLink>
          </div>

          <!-- Empty State -->
          <div
            v-if="!projectStore.loading && projectStore.recentProjects.length === 0"
            class="rounded-xl border border-dashed border-brand-border bg-[#FAF9F6] py-12 text-center"
          >
            <p class="font-mono text-xs uppercase tracking-widest text-brand-secondary">WORKSPACE EMPTY</p>
            <h4 class="mt-2 text-base font-bold text-brand-text">No landing page projects yet</h4>
            <p class="mx-auto mt-1 max-w-sm text-xs text-brand-muted">
              Draft your first page using the structured Copy Architect or start from a layout preset.
            </p>
            <div class="mt-5 flex justify-center gap-3">
              <RouterLink to="/generator">
                <BaseButton size="sm" variant="primary">
                  Open Copy Architect
                </BaseButton>
              </RouterLink>
              <RouterLink to="/templates">
                <BaseButton size="sm" variant="outline">Browse Layouts</BaseButton>
              </RouterLink>
            </div>
          </div>

          <!-- Recent Projects List -->
          <div v-else class="divide-y divide-brand-border">
            <div
              v-for="project in projectStore.recentProjects"
              :key="project.id"
              class="flex flex-col justify-between gap-4 py-4 first:pt-0 last:pb-0 sm:flex-row sm:items-center"
            >
              <div class="min-w-0">
                <div class="flex items-center gap-2.5">
                  <RouterLink
                    :to="`/projects/${project.id}/edit`"
                    class="truncate text-sm font-bold text-brand-text hover:text-brand-secondary"
                  >
                    {{ project.name }}
                  </RouterLink>
                  <BaseBadge :variant="project.status">
                    {{ project.status }}
                  </BaseBadge>
                </div>
                <p class="mt-1 truncate font-mono text-xs text-brand-muted">
                  /p/{{ project.slug }}
                  <span v-if="project.template" class="ml-2 font-sans text-brand-muted">
                    • {{ project.template.name }}
                  </span>
                </p>
              </div>

              <div class="flex shrink-0 items-center gap-2">
                <RouterLink
                  v-if="project.status === 'published'"
                  :to="`/p/${project.slug}`"
                  target="_blank"
                >
                  <BaseButton size="sm" variant="ghost">
                    Live URL ↗
                  </BaseButton>
                </RouterLink>
                <BaseButton
                  size="sm"
                  variant="outline"
                  @click="router.push(`/projects/${project.id}/edit`)"
                >
                  Open Studio →
                </BaseButton>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activity Audit Log -->
      <div>
        <div class="rounded-xl border border-brand-border bg-white p-6 shadow-card">
          <div class="mb-5 flex items-center justify-between border-b border-brand-border pb-3.5">
            <h3 class="text-base font-bold text-brand-text">Audit Log</h3>
            <span class="font-mono text-[10px] uppercase tracking-widest text-brand-muted">LIVE</span>
          </div>

          <div
            v-if="projectStore.recentActivity.length === 0"
            class="py-8 text-center font-mono text-xs text-brand-muted"
          >
            No workspace events recorded.
          </div>

          <ul v-else class="space-y-3.5">
            <li
              v-for="item in projectStore.recentActivity"
              :key="item.id"
              class="flex items-start gap-3 border-b border-brand-border pb-3 last:border-b-0 last:pb-0"
            >
              <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-secondary" />
              <div class="min-w-0 flex-1">
                <p class="text-xs font-medium text-brand-text">{{ item.description }}</p>
                <p class="mt-0.5 font-mono text-[10px] text-brand-muted">
                  {{ new Date(item.created_at).toLocaleString() }}
                </p>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>
