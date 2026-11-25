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
    published: 'bg-emerald-50/80 text-emerald-700 border-emerald-200/80',
    draft: 'bg-amber-50/80 text-amber-800 border-amber-200/80',
    archived: 'bg-zinc-100 text-zinc-600 border-zinc-200',
    ai: 'bg-zinc-900 text-white border-zinc-900',
    neutral: 'bg-zinc-100/80 text-zinc-700 border-zinc-200/80',
  };
  return `inline-flex items-center gap-1.5 rounded px-1.5 py-0.5 border font-mono text-[11px] font-medium leading-none ${styles[props.variant]}`;
});

const dotColor = computed(() => {
  const map: Record<string, string> = {
    published: 'bg-emerald-600',
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
