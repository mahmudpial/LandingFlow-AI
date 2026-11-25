<script setup lang="ts">
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useToastStore } from '@/stores/toast';
import { registerSchema } from '@/utils/validation';
import { extractErrorMessage, extractFieldErrors } from '@/services/api';
import BaseInput from '@/components/ui/BaseInput.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import ThemeToggle from '@/components/ui/ThemeToggle.vue';
import BrandLogo from '@/components/common/BrandLogo.vue';

const router = useRouter();
const authStore = useAuthStore();
const toastStore = useToastStore();

const form = reactive({
  name: '',
  email: '',
  company_name: '',
  job_title: '',
  password: '',
  password_confirmation: '',
});

const errors = ref<Record<string, string>>({});
const formError = ref('');

async function handleSubmit(): Promise<void> {
  errors.value = {};
  formError.value = '';

  const parsed = registerSchema.safeParse(form);
  if (!parsed.success) {
    for (const issue of parsed.error.issues) {
      const key = String(issue.path[0] || '');
      if (key) errors.value[key] = issue.message;
    }
    return;
  }

  try {
    await authStore.register(parsed.data);
    toastStore.success('Workspace initialized', 'Welcome to LandingFlow Studio.');
    router.push('/dashboard');
  } catch (err) {
    errors.value = extractFieldErrors(err);
    formError.value = extractErrorMessage(err, 'Registration failed. Please check the highlighted fields.');
  }
}
</script>

<template>
  <div class="grid min-h-screen bg-[#FAFAFA] text-zinc-900 dark:bg-[#09090B] dark:text-zinc-100 lg:grid-cols-12">
    <!-- Left Editorial Column (Adapts to Light & Dark Mode) -->
    <div class="hidden flex-col justify-between border-r border-zinc-200 bg-zinc-100/80 p-12 text-zinc-900 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100 lg:col-span-5 lg:flex">
      <RouterLink to="/">
        <BrandLogo size="md" subtitle="STUDIO" />
      </RouterLink>

      <div class="my-auto max-w-md">
        <p class="font-mono text-xs uppercase tracking-[0.18em] text-rose-600 dark:text-rose-500">
          Built for Builders
        </p>
        <h2 class="mt-4 text-3xl font-bold leading-tight tracking-tight text-zinc-900 dark:text-white sm:text-4xl">
          From product brief to live conversion page in minutes.
        </h2>
        <ul class="mt-6 space-y-3 text-sm text-zinc-600 dark:text-zinc-400">
          <li class="flex items-center gap-2.5">
            <span class="h-1.5 w-1.5 rounded-full bg-rose-600 dark:bg-rose-500" />
            <span>10-section schema-validated page architecture</span>
          </li>
          <li class="flex items-center gap-2.5">
            <span class="h-1.5 w-1.5 rounded-full bg-rose-600 dark:bg-rose-500" />
            <span>3 handcrafted editorial & SaaS layout systems</span>
          </li>
          <li class="flex items-center gap-2.5">
            <span class="h-1.5 w-1.5 rounded-full bg-rose-600 dark:bg-rose-500" />
            <span>Responsive live editor with 30-step Undo/Redo</span>
          </li>
        </ul>
      </div>

      <div class="flex items-center justify-between border-t border-zinc-200 pt-6 font-mono text-xs text-zinc-500 dark:border-white/10">
        <span>ZERO-COST LOCAL STUDIO</span>
        <span>AWS BEDROCK READY</span>
      </div>
    </div>

    <!-- Right Form Column -->
    <div class="relative flex flex-col justify-center px-4 py-10 sm:px-6 lg:col-span-7 lg:px-16">
      <div class="absolute right-6 top-6">
        <ThemeToggle :show-label="true" />
      </div>

      <div class="mx-auto w-full max-w-md animate-fade-up">
        <RouterLink to="/" class="mb-6 inline-flex lg:hidden">
          <BrandLogo size="md" subtitle="STUDIO" />
        </RouterLink>

        <div>
          <span class="font-mono text-[11px] font-semibold uppercase tracking-widest text-rose-600 dark:text-rose-400">
            New Workspace
          </span>
          <h1 class="mt-1.5 text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">
            Create your studio account
          </h1>
          <p class="mt-1.5 text-sm text-zinc-600 dark:text-zinc-400">
            Already registered?
            <RouterLink to="/login" class="font-semibold text-zinc-900 underline decoration-rose-500 underline-offset-4 hover:text-rose-600 dark:text-white dark:hover:text-rose-400">
              Sign in here
            </RouterLink>
          </p>
        </div>

        <div class="mt-6 rounded-xl border border-zinc-200 bg-white p-6 shadow-subtle dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
          <div
            v-if="formError"
            role="alert"
            class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-medium text-rose-700 dark:border-rose-800 dark:bg-rose-950/50 dark:text-rose-300"
          >
            {{ formError }}
          </div>

          <form class="space-y-4" @submit.prevent="handleSubmit">
            <BaseInput
              id="reg-name"
              v-model="form.name"
              label="Full Name"
              placeholder="Elena Vance"
              required
              :error="errors.name"
            />

            <BaseInput
              id="reg-email"
              v-model="form.email"
              label="Work Email"
              type="email"
              placeholder="elena@company.com"
              required
              :error="errors.email"
            />

            <div class="grid gap-4 sm:grid-cols-2">
              <BaseInput
                id="reg-company"
                v-model="form.company_name"
                label="Company / Studio"
                placeholder="Vance Labs"
                :error="errors.company_name"
              />
              <BaseInput
                id="reg-role"
                v-model="form.job_title"
                label="Role"
                placeholder="Founder"
                :error="errors.job_title"
              />
            </div>

            <BaseInput
              id="reg-password"
              v-model="form.password"
              label="Password"
              type="password"
              placeholder="8+ chars with letters & numbers (e.g. Studio2026)"
              required
              :error="errors.password"
            />

            <BaseInput
              id="reg-password-confirm"
              v-model="form.password_confirmation"
              label="Confirm Password"
              type="password"
              placeholder="Repeat password"
              required
              :error="errors.password_confirmation"
            />

            <div class="pt-2">
              <BaseButton type="submit" variant="primary" size="lg" full-width :loading="authStore.loading">
                Initialize Workspace
              </BaseButton>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>
