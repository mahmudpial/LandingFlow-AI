<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useProjectStore } from '@/stores/projects';
import { useToastStore } from '@/stores/toast';
import BaseButton from '@/components/ui/BaseButton.vue';
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
  <div class="flex min-h-screen bg-[#FAFAFA] text-zinc-900">
    <!-- Left Linear/Vercel-Density Sidebar (232px) -->
    <aside
      class="hidden w-[232px] shrink-0 flex-col border-r border-zinc-200/90 bg-[#F4F4F5]/70 lg:flex"
      aria-label="Primary Workspace Sidebar"
    >
      <!-- Workspace Switcher Header (48px) -->
      <div class="flex h-12 items-center justify-between border-b border-zinc-200/80 px-3.5">
        <RouterLink to="/dashboard" class="flex items-center gap-2">
          <img src="/logo.svg" alt="LandingFlow" class="h-5 w-5 rounded" />
          <span class="text-[13px] font-semibold tracking-tight text-zinc-900">
            {{ authStore.user?.company_name || 'LandingFlow' }}
          </span>
        </RouterLink>
        <span class="rounded border border-zinc-200 bg-white px-1.5 py-0.5 font-mono text-[10px] font-medium text-zinc-500">
          PRO
        </span>
      </div>

      <!-- Quick Command Search Trigger -->
      <div class="px-2.5 pt-3">
        <button
          type="button"
          class="flex h-8 w-full items-center justify-between rounded-md border border-zinc-200/90 bg-white px-2.5 text-xs text-zinc-500 shadow-subtle transition hover:border-zinc-300 hover:text-zinc-800"
          @click="commandPaletteOpen = true"
        >
          <span class="flex items-center gap-2">
            <Search class="h-3.5 w-3.5 text-zinc-400" />
            <span>Search or jump...</span>
          </span>
          <kbd class="kbd-shortcut">⌘K</kbd>
        </button>
      </div>

      <!-- Grouped Navigation -->
      <div class="flex-1 space-y-5 px-2.5 py-4">
        <div v-for="group in navGroups" :key="group.label">
          <p class="mb-1.5 px-2 font-mono text-[10px] font-medium uppercase tracking-wider text-zinc-400">
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
                  ? 'bg-zinc-900/6 font-medium text-zinc-950'
                  : 'text-zinc-600 hover:bg-zinc-200/50 hover:text-zinc-900'
              "
            >
              <div class="flex items-center gap-2">
                <span
                  class="h-1.5 w-1.5 rounded-full transition-colors"
                  :class="
                    route.path.startsWith(item.to)
                      ? 'bg-zinc-900'
                      : 'bg-transparent group-hover:bg-zinc-300'
                  "
                />
                <span>{{ item.name }}</span>
              </div>
              <span class="font-mono text-[10px] text-zinc-400 opacity-0 transition group-hover:opacity-100">
                {{ item.shortcut }}
              </span>
            </RouterLink>
          </div>
        </div>
      </div>

      <!-- Compact Footer User Strip -->
      <div class="border-t border-zinc-200/80 p-2.5">
        <div class="flex items-center justify-between rounded-md px-2 py-1.5 hover:bg-zinc-200/50">
          <div class="flex min-w-0 items-center gap-2">
            <div
              class="flex h-6 w-6 shrink-0 items-center justify-center rounded bg-zinc-900 font-mono text-[10px] font-semibold text-white"
            >
              {{ userInitials }}
            </div>
            <div class="min-w-0">
              <p class="truncate text-xs font-medium text-zinc-900">
                {{ authStore.user?.name }}
              </p>
              <p class="truncate font-mono text-[10px] text-zinc-500">
                {{ authStore.user?.email }}
              </p>
            </div>
          </div>
          <button
            type="button"
            class="rounded p-1 text-zinc-400 transition hover:bg-zinc-200 hover:text-zinc-800"
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
      <header class="sticky top-0 z-20 flex h-12 items-center justify-between border-b border-zinc-200/80 bg-white/85 px-4 backdrop-blur-md sm:px-6">
        <div class="flex items-center gap-2 text-xs">
          <RouterLink to="/dashboard" class="flex items-center gap-1.5 font-medium text-zinc-500 hover:text-zinc-900">
            <img src="/logo.svg" alt="LandingFlow" class="h-4 w-4 rounded lg:hidden" />
            <span>{{ authStore.user?.company_name || 'Workspace' }}</span>
          </RouterLink>
          <ChevronRight class="h-3.5 w-3.5 text-zinc-300" />
          <h1 class="font-semibold text-zinc-900">
            {{ route.meta.title || 'Overview' }}
          </h1>
        </div>

        <div class="flex items-center gap-2">
          <button
            type="button"
            class="inline-flex h-7 items-center gap-1.5 rounded-md border border-zinc-200 bg-zinc-50 px-2 text-xs text-zinc-500 hover:text-zinc-900 lg:hidden"
            @click="commandPaletteOpen = true"
          >
            <Command class="h-3 w-3" />
            <span>⌘K</span>
          </button>
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
        class="flex overflow-x-auto border-b border-zinc-200 bg-zinc-50 px-4 py-1.5 lg:hidden"
        aria-label="Mobile Navigation"
      >
        <RouterLink
          v-for="item in navGroups[0].items.concat(navGroups[1].items)"
          :key="item.to"
          :to="item.to"
          class="mr-1.5 shrink-0 rounded px-2.5 py-1 text-xs font-medium"
          :class="
            route.path.startsWith(item.to)
              ? 'bg-zinc-900 text-white'
              : 'text-zinc-600'
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
          class="fixed inset-0 bg-zinc-950/40 backdrop-blur-[1px] animate-fade-in"
          @click="commandPaletteOpen = false"
        />
        <div class="relative z-10 w-full max-w-lg overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-elevated animate-scale-in">
          <div class="flex items-center border-b border-zinc-100 px-3.5">
            <Search class="h-4 w-4 text-zinc-400" />
            <input
              v-model="commandQuery"
              type="text"
              autofocus
              placeholder="Type a command or search projects..."
              class="h-11 w-full bg-transparent px-3 text-[13px] text-zinc-900 placeholder-zinc-400 focus:outline-none"
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
              class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-[13px] text-zinc-700 transition hover:bg-zinc-100 hover:text-zinc-950"
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
