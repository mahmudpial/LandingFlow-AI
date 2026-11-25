<script setup lang="ts">
import { reactive, ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useToastStore } from '@/stores/toast';
import { loginSchema } from '@/utils/validation';
import { extractErrorMessage, extractFieldErrors } from '@/services/api';
import BaseInput from '@/components/ui/BaseInput.vue';
import BaseButton from '@/components/ui/BaseButton.vue';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const toastStore = useToastStore();

const form = reactive({
  email: '',
  password: '',
  remember: true,
});

const errors = ref<Record<string, string>>({});
const formError = ref('');

async function handleSubmit(): Promise<void> {
  errors.value = {};
  formError.value = '';

  const parsed = loginSchema.safeParse(form);
  if (!parsed.success) {
    for (const issue of parsed.error.issues) {
      const key = String(issue.path[0] || '');
      if (key) errors.value[key] = issue.message;
    }
    return;
  }

  try {
    await authStore.login(parsed.data);
    toastStore.success('Welcome back', 'Workspace session active.');
    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : '/dashboard';
    router.push(redirect);
  } catch (err) {
    errors.value = extractFieldErrors(err);
    formError.value = extractErrorMessage(err, 'Unable to sign in. Please verify your credentials.');
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
          Conversion Architecture
        </p>
        <h2 class="mt-4 text-3xl font-bold leading-tight tracking-tight text-white sm:text-4xl">
          Structured landing pages built with editorial precision.
        </h2>
        <p class="mt-4 text-sm leading-relaxed text-[#A1A09A]">
          Draft persuasive product messaging, test responsive layouts across viewports, and publish
          directly to clean public slugs.
        </p>
      </div>

      <div class="flex items-center justify-between border-t border-white/10 pt-6 font-mono text-xs text-[#8E8D87]">
        <span>SANCTUM STATEFUL AUTH</span>
        <span>v1.0 STUDIO</span>
      </div>
    </div>

    <!-- Right Form Column -->
    <div class="flex flex-col justify-center px-4 py-12 sm:px-6 lg:col-span-7 lg:px-16">
      <div class="mx-auto w-full max-w-md animate-fade-up">
        <RouterLink to="/" class="mb-8 inline-flex items-center gap-2 lg:hidden">
          <img src="/logo.svg" alt="LandingFlow" class="h-7 w-7 rounded-md" />
          <span class="text-base font-bold text-brand-text">LandingFlow Studio</span>
        </RouterLink>

        <div>
          <span class="font-mono text-[11px] font-semibold uppercase tracking-widest text-brand-secondary">
            Workspace Access
          </span>
          <h1 class="mt-1.5 text-2xl font-bold tracking-tight text-brand-text">
            Sign in to your studio
          </h1>
          <p class="mt-1.5 text-sm text-brand-muted">
            New to LandingFlow?
            <RouterLink to="/register" class="font-semibold text-brand-text underline decoration-brand-secondary underline-offset-4 hover:text-brand-secondary">
              Create an account
            </RouterLink>
          </p>
        </div>

        <div class="mt-8 rounded-xl border border-brand-border bg-white p-6 shadow-card sm:p-8">
          <div
            v-if="formError"
            role="alert"
            class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-medium text-brand-error"
          >
            {{ formError }}
          </div>

          <form class="space-y-5" @submit.prevent="handleSubmit">
            <BaseInput
              id="login-email"
              v-model="form.email"
              label="Work Email"
              type="email"
              placeholder="founder@company.com"
              required
              :error="errors.email"
            />

            <BaseInput
              id="login-password"
              v-model="form.password"
              label="Password"
              type="password"
              placeholder="••••••••"
              required
              :error="errors.password"
            />

            <div class="flex items-center justify-between text-xs">
              <label class="flex items-center gap-2 text-brand-muted">
                <input
                  v-model="form.remember"
                  type="checkbox"
                  class="h-4 w-4 rounded border-brand-border text-brand-primary focus:ring-brand-primary"
                />
                <span>Keep session active</span>
              </label>
              <RouterLink to="/forgot-password" class="font-semibold text-brand-text hover:text-brand-secondary">
                Reset password
              </RouterLink>
            </div>

            <BaseButton type="submit" variant="primary" size="lg" full-width :loading="authStore.loading">
              Enter Workspace
            </BaseButton>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>
