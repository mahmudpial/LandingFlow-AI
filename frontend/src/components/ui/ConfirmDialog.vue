<script setup lang="ts">
import BaseModal from './BaseModal.vue';
import BaseButton from './BaseButton.vue';
import { AlertTriangle } from 'lucide-vue-next';

withDefaults(
  defineProps<{
    open: boolean;
    title: string;
    message: string;
    confirmLabel?: string;
    cancelLabel?: string;
    variant?: 'danger' | 'primary';
    loading?: boolean;
  }>(),
  {
    confirmLabel: 'Confirm',
    cancelLabel: 'Cancel',
    variant: 'danger',
    loading: false,
  }
);

defineEmits<{
  (e: 'confirm'): void;
  (e: 'cancel'): void;
}>();
</script>

<template>
  <BaseModal :open="open" :title="title" max-width="md" @close="$emit('cancel')">
    <div class="flex items-start gap-3.5">
      <div
        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full"
        :class="variant === 'danger' ? 'bg-red-50 text-brand-error' : 'bg-indigo-50 text-brand-primary'"
      >
        <AlertTriangle class="h-5 w-5" />
      </div>
      <p class="text-sm leading-relaxed text-slate-600">
        {{ message }}
      </p>
    </div>
    <div class="mt-6 flex items-center justify-end gap-2.5">
      <BaseButton variant="outline" :disabled="loading" @click="$emit('cancel')">
        {{ cancelLabel }}
      </BaseButton>
      <BaseButton :variant="variant" :loading="loading" @click="$emit('confirm')">
        {{ confirmLabel }}
      </BaseButton>
    </div>
  </BaseModal>
</template>
