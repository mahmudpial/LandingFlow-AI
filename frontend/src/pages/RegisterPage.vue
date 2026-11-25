<script setup lang="ts">
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useToastStore } from '@/stores/toast';
import { registerSchema } from '@/utils/validation';
import { extractErrorMessage, extractFieldErrors } from '@/services/api';
import BaseInput from '@/components/ui/BaseInput.vue';
import BaseButton from '@/components/ui/BaseButton.vue';

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
  <div class="grid min-h-screen bg-brand-bg lg:grid-cols-12">
    <!-- Left Editorial Column -->
    <div class="hidden flex-col justify-between border-r border-brand-border bg-[#111318] p-12 text-[#FAF9F5] lg:col-span-5 lg:flex">
      <RouterLink to="/" class="flex items-center gap-2.5">
        <img src="/logo.svg" alt="LandingFlow" class="h-8 w-8 rounded-md" />
        <span class="text-base font-bold tracking-tight">LandingFlow Studio</span>
      </RouterLink>

      <div class="my-auto max-w-md">
        <p class="font-mono text-xs uppercase tracking-[0.18em] text-brand-secondary">
          Built for Builders
        </p>
        <h2 class="mt-4 text-3xl font-bold leading-tight tracking-tight text-white sm:text-4xl">
          From product brief to live conversion page in minutes.
        </h2>
        <ul class="mt-6 space-y-3 text-sm text-[#A1A09A]">
          <li class="flex items-center gap-2.5">
            <span class="h-1.5 w-1.5 rounded-full bg-brand-secondary" />
            <span>10-section schema-validated page architecture</span>
          </li>
          <li class="flex items-center gap-2.5">
            <span class="h-1.5 w-1.5 rounded-full bg-brand-secondary" />
            <span>3 handcrafted editorial & SaaS layout systems</span>
          </li>
          <li class="flex items-center gap-2.5">
            <span class="h-1.5 w-1.5 rounded-full bg-brand-secondary" />
            <span>Responsive live editor with 30-step Undo/Redo</span>
          </li>
        </ul>
      </div>

      <div class="flex items-center justify-between border-t border-white/10 pt-6 font-mono text-xs text-[#8E8D87]">
        <span>ZERO-COST LOCAL STUDIO</span>
        <span>AWS BEDROCK READY</span>
      </div>
    </div>

    <!-- Right Form Column -->
    <div class="flex flex-col justify-center px-4 py-10 sm:px-6 lg:col-span-7 lg:px-16">
      <div class="mx-auto w-full max-w-md animate-fade-up">
        <RouterLink to="/" class="mb-6 inline-flex items-center gap-2 lg:hidden">
          <img src="/logo.svg" alt="LandingFlow" class="h-7 w-7 rounded-md" />
          <span class="text-base font-bold text-brand-text">LandingFlow Studio</span>
        </RouterLink>

        <div>
          <span class="font-mono text-[11px] font-semibold uppercase tracking-widest text-brand-secondary">
            New Workspace
          </span>
          <h1 class="mt-1.5 text-2xl font-bold tracking-tight text-brand-text">
            Create your studio account
          </h1>
          <p class="mt-1.5 text-sm text-brand-muted">
            Already registered?
            <RouterLink to="/login" class="font-semibold text-brand-text underline decoration-brand-secondary underline-offset-4 hover:text-brand-secondary">
              Sign in here
            </RouterLink>
          </p>
        </div>

        <div class="mt-6 rounded-xl border border-brand-border bg-white p-6 shadow-card sm:p-8">
          <div
            v-if="formError"
            role="alert"
            class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-medium text-brand-error"
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
