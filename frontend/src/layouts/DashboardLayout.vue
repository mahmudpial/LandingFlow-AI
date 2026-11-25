<script setup lang="ts">
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useToastStore } from '@/stores/toast';
import BaseButton from '@/components/ui/BaseButton.vue';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const toastStore = useToastStore();

const navItems = [
  { name: 'Overview', to: '/dashboard', code: '01' },
  { name: 'Projects', to: '/projects', code: '02' },
  { name: 'Templates', to: '/templates', code: '03' },
  { name: 'Copy Architect', to: '/generator', code: '04' },
  { name: 'Account', to: '/settings', code: '05' },
  { name: 'System Specs', to: '/help', code: '06' },
];

const userInitials = computed(() => {
  const name = authStore.user?.name || 'LF';
  return name
    .split(' ')
    .map((p) => p[0])
    .join('')
    .toUpperCase()
    .slice(0, 2);
});

async function handleLogout(): Promise<void> {
  await authStore.logout();
  toastStore.success('Session closed', 'You have been signed out.');
  router.push('/login');
}
</script>

<template>
  <div class="flex min-h-screen bg-brand-bg text-brand-text">
    <!-- Left Architectural Sidebar -->
    <aside
      class="hidden w-64 shrink-0 flex-col border-r border-brand-border bg-[#F3F1EC] lg:flex"
      aria-label="Workspace Navigation"
    >
      <!-- Brand Header -->
      <div class="flex h-16 items-center justify-between border-b border-brand-border px-5">
        <RouterLink to="/dashboard" class="flex items-center gap-2.5">
          <img src="/logo.svg" alt="LandingFlow" class="h-7 w-7 rounded-md" />
          <div class="flex items-baseline gap-1.5">
            <span class="text-[15px] font-extrabold tracking-tight text-brand-text">LandingFlow</span>
            <span class="font-mono text-[10px] font-semibold uppercase tracking-widest text-brand-secondary">
              Studio
            </span>
          </div>
        </RouterLink>
      </div>

      <!-- Navigation Section -->
      <div class="flex-1 px-3 py-5">
        <p class="mb-2.5 px-3 font-mono text-[10px] font-semibold uppercase tracking-[0.14em] text-brand-muted">
          Workspace
        </p>
        <div class="space-y-1">
          <RouterLink
            v-for="item in navItems"
            :key="item.to"
            :to="item.to"
            class="group flex items-center justify-between rounded-lg px-3 py-2 text-[13px] font-medium transition-all duration-150"
            :class="
              route.path.startsWith(item.to)
                ? 'bg-white text-brand-text font-semibold shadow-card border border-brand-border'
                : 'text-brand-muted hover:bg-[#E8E5DE]/70 hover:text-brand-text'
            "
          >
            <div class="flex items-center gap-2.5">
              <span
                class="h-1.5 w-1.5 rounded-full transition-colors"
                :class="
                  route.path.startsWith(item.to)
                    ? 'bg-brand-secondary'
                    : 'bg-transparent group-hover:bg-[#CFCBC2]'
                "
              />
              <span>{{ item.name }}</span>
            </div>
            <span class="font-mono text-[10px] text-[#9E9B95]">{{ item.code }}</span>
          </RouterLink>
        </div>
      </div>

      <!-- Bottom User Profile & Sign Out -->
      <div class="border-t border-brand-border bg-[#ECE9E2]/60 p-3.5">
        <div class="flex items-center justify-between gap-2 rounded-lg border border-brand-border bg-white p-2.5 shadow-2xs">
          <div class="flex min-w-0 items-center gap-2.5">
            <div
              class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-brand-primary font-mono text-xs font-semibold text-[#FAF9F5]"
            >
              {{ userInitials }}
            </div>
            <div class="min-w-0">
              <p class="truncate text-xs font-bold text-brand-text">
                {{ authStore.user?.name }}
              </p>
              <p class="truncate font-mono text-[10px] text-brand-muted">
                {{ authStore.user?.email }}
              </p>
            </div>
          </div>
          <button
            type="button"
            class="rounded-md px-2 py-1 font-mono text-[10px] font-semibold uppercase tracking-wider text-brand-muted transition hover:bg-rose-50 hover:text-brand-error"
            title="Sign out"
            @click="handleLogout"
          >
            Exit
          </button>
        </div>
      </div>
    </aside>

    <!-- Main Workspace Column -->
    <div class="flex min-w-0 flex-1 flex-col">
      <!-- Top Bar -->
      <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-brand-border bg-brand-bg/90 px-4 backdrop-blur-md sm:px-6 lg:px-8">
        <div class="flex items-center gap-3">
          <RouterLink to="/dashboard" class="flex items-center gap-2 lg:hidden">
            <img src="/logo.svg" alt="LandingFlow" class="h-6 w-6 rounded" />
            <span class="text-sm font-bold text-brand-text">LandingFlow</span>
          </RouterLink>
          <div class="hidden h-4 w-px bg-brand-border lg:block" />
          <div class="flex items-baseline gap-2">
            <span class="font-mono text-[11px] uppercase tracking-widest text-brand-muted">
              Studio /
            </span>
            <h1 class="text-sm font-bold tracking-tight text-brand-text sm:text-base">
              {{ route.meta.title || 'Workspace' }}
            </h1>
          </div>
        </div>

        <div class="flex items-center gap-2.5">
          <RouterLink to="/projects">
            <BaseButton size="sm" variant="outline">Projects</BaseButton>
          </RouterLink>
          <RouterLink to="/generator">
            <BaseButton size="sm" variant="primary">
              <span>+ New Page Brief</span>
            </BaseButton>
          </RouterLink>
        </div>
      </header>

      <!-- Mobile Horizontal Nav -->
      <nav
        class="flex overflow-x-auto border-b border-brand-border bg-[#F3F1EC] px-4 py-2 lg:hidden"
        aria-label="Mobile Navigation"
      >
        <RouterLink
          v-for="item in navItems"
          :key="item.to"
          :to="item.to"
          class="mr-2 shrink-0 rounded-md px-2.5 py-1 text-xs font-medium"
          :class="
            route.path.startsWith(item.to)
              ? 'bg-white text-brand-text font-semibold shadow-2xs'
              : 'text-brand-muted'
          "
        >
          {{ item.name }}
        </RouterLink>
      </nav>

      <!-- Main View Content -->
      <main class="mx-auto w-full max-w-7xl flex-1 p-4 sm:p-6 lg:p-8 animate-fade-up">
        <slot />
      </main>
    </div>
  </div>
</template>
