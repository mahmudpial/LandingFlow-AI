<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useProjectStore } from '@/stores/projects';
import { useToastStore } from '@/stores/toast';
import { useThemeStore } from '@/stores/theme';
import BaseButton from '@/components/ui/BaseButton.vue';
import ThemeToggle from '@/components/ui/ThemeToggle.vue';
import BrandLogo from '@/components/common/BrandLogo.vue';
import {
  Search,
  Plus,
  ChevronRight,
  Command,
  LogOut,
  ArrowUpRight,
} from 'lucide-vue-next';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const projectStore = useProjectStore();
const toastStore = useToastStore();
const themeStore = useThemeStore();

const commandPaletteOpen = ref(false);
const commandQuery = ref('');

const navGroups = [
  {
    label: 'Workspace',
    items: [
      { name: 'Overview', to: '/dashboard', shortcut: 'G O' },
      { name: 'Projects', to: '/projects', shortcut: 'G P' },
      { name: 'Layout Systems', to: '/templates', shortcut: 'G T' },
      { name: 'Copy Architect', to: '/generator', shortcut: 'G C' },
    ],
  },
  {
    label: 'Configuration',
    items: [
      { name: 'Account & Security', to: '/settings', shortcut: 'G S' },
      { name: 'Architecture Specs', to: '/help', shortcut: 'G H' },
    ],
  },
];

const commandItems = computed(() => {
  const q = commandQuery.value.trim().toLowerCase();
  const baseActions = [
    { label: 'Draft new page with Copy Architect', category: 'Action', to: '/generator' },
    { label: 'Toggle Dark / Light Theme', category: 'Appearance', to: '#toggle-theme' },
    { label: 'Go to Workspace Overview', category: 'Navigation', to: '/dashboard' },
    { label: 'View All Landing Page Projects', category: 'Navigation', to: '/projects' },
    { label: 'Browse Layout Systems & Templates', category: 'Navigation', to: '/templates' },
    { label: 'Account & Session Security Settings', category: 'Navigation', to: '/settings' },
  ];

  const projectActions = projectStore.projects.map((p) => ({
    label: `Edit project: ${p.name}`,
    category: `Project (${p.status})`,
    to: `/projects/${p.id}/edit`,
  }));

  const all = [...baseActions, ...projectActions];
  if (!q) return all.slice(0, 8);
  return all.filter(
    (item) =>
      item.label.toLowerCase().includes(q) || item.category.toLowerCase().includes(q)
  );
});

const userInitials = computed(() => {
  const name = authStore.user?.name || 'LF';
  return name
    .split(' ')
    .map((p) => p[0])
    .join('')
    .toUpperCase()
    .slice(0, 2);
});

function handleGlobalKeydown(e: KeyboardEvent): void {
  if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
    e.preventDefault();
    commandPaletteOpen.value = !commandPaletteOpen.value;
    commandQuery.value = '';
  } else if (e.key === 'Escape' && commandPaletteOpen.value) {
    commandPaletteOpen.value = false;
  }
}

function executeCommand(to: string): void {
  commandPaletteOpen.value = false;
  if (to === '#toggle-theme') {
    themeStore.toggleTheme();
    return;
  }
  router.push(to);
}

onMounted(() => window.addEventListener('keydown', handleGlobalKeydown));
onUnmounted(() => window.removeEventListener('keydown', handleGlobalKeydown));

async function handleLogout(): Promise<void> {
  await authStore.logout();
  toastStore.success('Session closed', 'You have been signed out.');
  router.push('/login');
}
</script>

<template>
  <div class="flex min-h-screen bg-[#FAFAFA] text-zinc-900 dark:bg-[#09090B] dark:text-zinc-100">
    <!-- Left Linear/Vercel-Density Sidebar (232px) -->
    <aside
      class="hidden w-[232px] shrink-0 flex-col border-r border-zinc-200/90 bg-[#F4F4F5]/80 dark:border-zinc-800 dark:bg-zinc-900/90 lg:flex"
      aria-label="Primary Workspace Sidebar"
    >
      <!-- Workspace Switcher Header (48px) -->
      <div class="flex h-12 items-center justify-between border-b border-zinc-200/80 px-3.5 dark:border-zinc-800">
        <RouterLink to="/dashboard">
          <BrandLogo size="sm" subtitle="PRO" />
        </RouterLink>
      </div>

      <!-- Quick Command Search Trigger -->
      <div class="px-2.5 pt-3">
        <button
          type="button"
          class="flex h-8 w-full items-center justify-between rounded-md border border-zinc-200/90 bg-white px-2.5 text-xs text-zinc-500 shadow-subtle transition hover:border-zinc-300 hover:text-zinc-900 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-400 dark:hover:border-zinc-700 dark:hover:text-zinc-200"
          @click="commandPaletteOpen = true"
        >
          <span class="flex items-center gap-2">
            <Search class="h-3.5 w-3.5 text-zinc-400 dark:text-zinc-500" />
            <span>Search or jump...</span>
          </span>
          <kbd class="kbd-shortcut">⌘K</kbd>
        </button>
      </div>

      <!-- Grouped Navigation -->
      <div class="flex-1 space-y-5 px-2.5 py-4">
        <div v-for="group in navGroups" :key="group.label">
          <p class="mb-1.5 px-2 font-mono text-[10px] font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
            {{ group.label }}
          </p>
          <div class="space-y-0.5">
            <RouterLink
              v-for="item in group.items"
              :key="item.to"
              :to="item.to"
              class="group flex h-8 items-center justify-between rounded-md px-2 text-[13px] transition-colors"
              :class="
                route.path.startsWith(item.to)
                  ? 'bg-zinc-900 text-white font-medium dark:bg-zinc-100 dark:text-zinc-950'
                  : 'text-zinc-700 hover:bg-zinc-200/70 hover:text-zinc-950 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white'
              "
            >
              <div class="flex items-center gap-2">
                <span
                  class="h-1.5 w-1.5 rounded-full transition-colors"
                  :class="
                    route.path.startsWith(item.to)
                      ? 'bg-rose-500'
                      : 'bg-transparent group-hover:bg-zinc-400 dark:group-hover:bg-zinc-500'
                  "
                />
                <span>{{ item.name }}</span>
              </div>
              <span
                class="font-mono text-[10px] opacity-0 transition group-hover:opacity-100"
                :class="route.path.startsWith(item.to) ? 'text-zinc-300 dark:text-zinc-600' : 'text-zinc-400 dark:text-zinc-500'"
              >
                {{ item.shortcut }}
              </span>
            </RouterLink>
          </div>
        </div>
      </div>

      <!-- Compact Footer User Strip -->
      <div class="border-t border-zinc-200/80 p-2.5 dark:border-zinc-800">
        <div class="flex items-center justify-between rounded-md px-2 py-1.5 hover:bg-zinc-200/50 dark:hover:bg-zinc-800/70">
          <div class="flex min-w-0 items-center gap-2">
            <div
              class="flex h-6 w-6 shrink-0 items-center justify-center rounded bg-zinc-900 font-mono text-[10px] font-semibold text-white dark:bg-zinc-100 dark:text-zinc-950"
            >
              {{ userInitials }}
            </div>
            <div class="min-w-0">
              <p class="truncate text-xs font-medium text-zinc-900 dark:text-zinc-100">
                {{ authStore.user?.name }}
              </p>
              <p class="truncate font-mono text-[10px] text-zinc-500 dark:text-zinc-400">
                {{ authStore.user?.email }}
              </p>
            </div>
          </div>
          <button
            type="button"
            class="rounded p-1 text-zinc-500 transition hover:bg-zinc-200 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-700 dark:hover:text-white"
            title="Sign out"
            aria-label="Sign out"
            @click="handleLogout"
          >
            <LogOut class="h-3.5 w-3.5" />
          </button>
        </div>
      </div>
    </aside>

    <!-- Main Workspace Column -->
    <div class="flex min-w-0 flex-1 flex-col">
      <!-- Top Breadcrumb & Context Bar (48px) -->
      <header class="sticky top-0 z-20 flex h-12 items-center justify-between border-b border-zinc-200/80 bg-white/90 px-4 backdrop-blur-md dark:border-zinc-800 dark:bg-zinc-900/90 sm:px-6">
        <div class="flex items-center gap-2 text-xs">
          <RouterLink to="/dashboard" class="flex items-center gap-1.5 font-medium text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">
            <img src="/logo.svg" alt="LandingFlow" class="h-4 w-4 rounded lg:hidden" />
            <span>{{ authStore.user?.company_name || 'Workspace' }}</span>
          </RouterLink>
          <ChevronRight class="h-3.5 w-3.5 text-zinc-300 dark:text-zinc-600" />
          <h1 class="font-semibold text-zinc-900 dark:text-zinc-100">
            {{ route.meta.title || 'Overview' }}
          </h1>
        </div>

        <div class="flex items-center gap-2">
          <button
            type="button"
            class="inline-flex h-8 items-center gap-1.5 rounded-md border border-zinc-200 bg-zinc-50 px-2 text-xs text-zinc-600 hover:text-zinc-900 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:text-white lg:hidden"
            @click="commandPaletteOpen = true"
          >
            <Command class="h-3 w-3" />
            <span>⌘K</span>
          </button>

          <!-- Global Dark / Light Theme Toggle -->
          <ThemeToggle :show-label="true" />

          <RouterLink to="/generator">
            <BaseButton size="sm" variant="primary">
              <Plus class="h-3.5 w-3.5" />
              <span>New Page</span>
            </BaseButton>
          </RouterLink>
        </div>
      </header>

      <!-- Mobile Sub-navigation -->
      <nav
        class="flex overflow-x-auto border-b border-zinc-200 bg-zinc-50 px-4 py-1.5 dark:border-zinc-800 dark:bg-zinc-900 lg:hidden"
        aria-label="Mobile Navigation"
      >
        <RouterLink
          v-for="item in navGroups[0].items.concat(navGroups[1].items)"
          :key="item.to"
          :to="item.to"
          class="mr-1.5 shrink-0 rounded px-2.5 py-1 text-xs font-medium"
          :class="
            route.path.startsWith(item.to)
              ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-950'
              : 'text-zinc-600 dark:text-zinc-300'
          "
        >
          {{ item.name }}
        </RouterLink>
      </nav>

      <!-- Main Page Slot -->
      <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-6 sm:px-6 lg:py-8 animate-fade-up">
        <slot />
      </main>
    </div>

    <!-- Global ⌘K Command Palette Modal -->
    <Teleport to="body">
      <div
        v-if="commandPaletteOpen"
        class="fixed inset-0 z-50 flex items-start justify-center pt-[14vh] p-4"
        role="dialog"
        aria-modal="true"
        aria-label="Command Palette"
      >
        <div
          class="fixed inset-0 bg-zinc-950/60 backdrop-blur-[2px] animate-fade-in"
          @click="commandPaletteOpen = false"
        />
        <div class="relative z-10 w-full max-w-lg overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-elevated animate-scale-in dark:border-zinc-800 dark:bg-zinc-900">
          <div class="flex items-center border-b border-zinc-100 px-3.5 dark:border-zinc-800">
            <Search class="h-4 w-4 text-zinc-400" />
            <input
              v-model="commandQuery"
              type="text"
              autofocus
              placeholder="Type a command or search projects..."
              class="h-11 w-full bg-transparent px-3 text-[13px] text-zinc-900 placeholder-zinc-400 focus:outline-none dark:text-zinc-100 dark:placeholder-zinc-500"
            />
            <kbd class="kbd-shortcut">ESC</kbd>
          </div>

          <div class="max-h-72 overflow-y-auto p-1.5">
            <div
              v-if="commandItems.length === 0"
              class="py-8 text-center text-xs text-zinc-400"
            >
              No matching commands or projects.
            </div>
            <button
              v-for="cmd in commandItems"
              :key="cmd.to + cmd.label"
              type="button"
              class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-[13px] text-zinc-700 transition hover:bg-zinc-100 hover:text-zinc-950 dark:text-zinc-200 dark:hover:bg-zinc-800 dark:hover:text-white"
              @click="executeCommand(cmd.to)"
            >
              <span class="font-medium">{{ cmd.label }}</span>
              <span class="flex items-center gap-1 font-mono text-[10px] text-zinc-400">
                {{ cmd.category }}
                <ArrowUpRight class="h-3 w-3" />
              </span>
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
