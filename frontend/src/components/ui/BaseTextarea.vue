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
      class="mb-1.5 block text-xs font-medium text-zinc-700"
    >
      {{ label }}
      <span v-if="required" class="ml-0.5 text-rose-600" aria-hidden="true">*</span>
    </label>
    <textarea
      :id="id"
      :rows="rows"
      :value="modelValue"
      :placeholder="placeholder"
      :required="required"
      :disabled="disabled"
      :aria-invalid="Boolean(error)"
      class="w-full rounded-md border px-2.5 py-2 text-[13px] leading-relaxed text-zinc-900 placeholder-zinc-400 shadow-subtle transition-colors duration-150 focus:outline-none focus:ring-2 disabled:bg-zinc-100"
      :class="
        error
          ? 'border-rose-500 bg-rose-50/20 focus:border-rose-600 focus:ring-rose-500/15'
          : 'border-zinc-200 bg-white hover:border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900/10'
      "
      @input="$emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
    />
    <p v-if="error" role="alert" class="mt-1 text-xs font-medium text-rose-600">
      {{ error }}
    </p>
    <p v-else-if="hint" class="mt-1 text-[11px] text-zinc-500">
      {{ hint }}
    </p>
  </div>
</template>
