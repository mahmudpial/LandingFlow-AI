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
    published: 'bg-[#ECFDF3] text-[#166534] border border-[#BBF7D0]',
    draft: 'bg-[#FEF3C7]/60 text-[#92400E] border border-[#FDE68A]',
    archived: 'bg-[#EFECE6] text-[#575653] border border-[#E4E2DC]',
    ai: 'bg-[#FFF3EE] text-[#C2411F] border border-[#FCD9CE]',
    neutral: 'bg-[#F3F1EC] text-[#3F3E3C] border border-[#E4E2DC]',
  };
  return `inline-flex items-center gap-1.5 rounded-md px-2 py-0.5 font-mono text-[11px] font-medium tracking-tight ${styles[props.variant]}`;
});

const dotColor = computed(() => {
  const map: Record<string, string> = {
    published: 'bg-[#15803D]',
    draft: 'bg-[#D97706]',
    archived: 'bg-[#78716C]',
    ai: 'bg-[#D94E28]',
    neutral: 'bg-[#78716C]',
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
