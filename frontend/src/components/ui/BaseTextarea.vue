<script setup lang="ts">
withDefaults(
  defineProps<{
    modelValue: string;
    id?: string;
    label?: string;
    rows?: number;
    placeholder?: string;
    error?: string;
    hint?: string;
    required?: boolean;
    disabled?: boolean;
  }>(),
  {
    rows: 3,
    placeholder: '',
    required: false,
    disabled: false,
  }
);

defineEmits<{
  (e: 'update:modelValue', value: string): void;
}>();
</script>

<template>
  <div class="w-full">
    <label
      v-if="label"
      :for="id"
      class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.06em] text-brand-muted"
    >
      {{ label }}
      <span v-if="required" class="text-brand-secondary" aria-hidden="true">*</span>
    </label>
    <textarea
      :id="id"
      :rows="rows"
      :value="modelValue"
      :placeholder="placeholder"
      :required="required"
      :disabled="disabled"
      :aria-invalid="Boolean(error)"
      class="w-full rounded-lg border px-3.5 py-2 text-sm text-brand-text placeholder-[#9E9B95] transition-all duration-150 focus:outline-none focus:ring-2 disabled:bg-[#EFECE6]"
      :class="
        error
          ? 'border-brand-error bg-rose-50/30 focus:border-brand-error focus:ring-brand-error/15'
          : 'border-brand-border bg-white hover:border-[#CFCBC2] focus:border-brand-primary focus:ring-brand-primary/10'
      "
      @input="$emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
    />
    <p v-if="error" role="alert" class="mt-1.5 text-xs font-medium text-brand-error">
      {{ error }}
    </p>
    <p v-else-if="hint" class="mt-1 text-xs text-brand-muted">
      {{ hint }}
    </p>
  </div>
</template>
