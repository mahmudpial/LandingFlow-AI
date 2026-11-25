<script setup lang="ts">
import { useThemeStore } from '@/stores/theme';
import { Sun, Moon } from 'lucide-vue-next';

withDefaults(
  defineProps<{
    showLabel?: boolean;
  }>(),
  {
    showLabel: false,
  }
);

const themeStore = useThemeStore();
</script>

<template>
  <button
    type="button"
    class="inline-flex h-8 items-center justify-center gap-1.5 rounded-md border border-zinc-200 bg-white px-2.5 text-xs font-medium text-zinc-700 shadow-subtle transition-colors hover:bg-zinc-100 hover:text-zinc-950 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-zinc-900 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800 dark:hover:text-white dark:focus-visible:ring-zinc-100"
    :title="themeStore.isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
    :aria-label="themeStore.isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
    @click="themeStore.toggleTheme()"
  >
    <Sun v-if="themeStore.isDark" class="h-3.5 w-3.5 text-amber-400" />
    <Moon v-else class="h-3.5 w-3.5 text-zinc-600" />
    <span v-if="showLabel">{{ themeStore.isDark ? 'Light' : 'Dark' }}</span>
  </button>
</template>
