<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
  defineProps<{
    variant?: 'published' | 'draft' | 'archived' | 'ai' | 'neutral';
  }>(),
  {
    variant: 'neutral',
  }
);

const badgeClasses = computed(() => {
  const styles: Record<string, string> = {
    published:
      'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800/70',
    draft:
      'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800/70',
    archived:
      'bg-zinc-100 text-zinc-600 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700',
    ai:
      'bg-zinc-900 text-white border-zinc-900 dark:bg-zinc-100 dark:text-zinc-950 dark:border-zinc-100',
    neutral:
      'bg-zinc-100 text-zinc-700 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-200 dark:border-zinc-700',
  };
  return `inline-flex items-center gap-1.5 rounded px-1.5 py-0.5 border font-mono text-[11px] font-medium leading-none ${styles[props.variant]}`;
});

const dotColor = computed(() => {
  const map: Record<string, string> = {
    published: 'bg-emerald-500',
    draft: 'bg-amber-500',
    archived: 'bg-zinc-400',
    ai: 'bg-rose-500',
    neutral: 'bg-zinc-400',
  };
  return map[props.variant];
});
</script>

<template>
  <span :class="badgeClasses">
    <span class="h-1.5 w-1.5 rounded-full" :class="dotColor" aria-hidden="true" />
    <slot />
  </span>
</template>
