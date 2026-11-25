<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue';
import { X } from 'lucide-vue-next';

const props = defineProps<{
  open: boolean;
  title: string;
  maxWidth?: 'md' | 'lg' | 'xl' | '2xl';
}>();

const emit = defineEmits<{
  (e: 'close'): void;
}>();

function handleKeydown(event: KeyboardEvent): void {
  if (event.key === 'Escape' && props.open) {
    emit('close');
  }
}

onMounted(() => window.addEventListener('keydown', handleKeydown));
onUnmounted(() => window.removeEventListener('keydown', handleKeydown));
</script>

<template>
  <Teleport to="body">
    <div
      v-if="open"
      class="fixed inset-0 z-50 flex items-center justify-center p-4"
      role="dialog"
      aria-modal="true"
      :aria-label="title"
    >
      <div
        class="fixed inset-0 bg-zinc-950/60 backdrop-blur-[2px] animate-fade-in"
        @click="$emit('close')"
      />
      <div
        class="relative z-10 w-full rounded-xl border border-zinc-200 bg-white p-6 text-zinc-900 shadow-elevated animate-scale-in dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100"
        :class="{
          'max-w-md': !maxWidth || maxWidth === 'md',
          'max-w-lg': maxWidth === 'lg',
          'max-w-xl': maxWidth === 'xl',
          'max-w-2xl': maxWidth === '2xl',
        }"
      >
        <div class="mb-5 flex items-center justify-between border-b border-zinc-200 pb-3.5 dark:border-zinc-800">
          <h3 class="text-base font-bold tracking-tight text-zinc-900 dark:text-zinc-100">{{ title }}</h3>
          <button
            type="button"
            class="rounded-md p-1 text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100"
            aria-label="Close modal"
            @click="$emit('close')"
          >
            <X class="h-4 w-4" />
          </button>
        </div>
        <div>
          <slot />
        </div>
      </div>
    </div>
  </Teleport>
</template>
