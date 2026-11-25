<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { apiClient, extractErrorMessage } from '@/services/api';
import type { ApiResponse, LandingPageContent, LandingPageTheme } from '@/types';
import LandingPageRenderer from '@/components/landing/LandingPageRenderer.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import { AlertCircle, Sparkles } from 'lucide-vue-next';

const route = useRoute();
const loading = ref(true);
const notFoundError = ref('');

const snapshot = ref<{
  project_name: string;
  slug: string;
  content: LandingPageContent;
  theme: LandingPageTheme;
  seo: { title: string; description: string };
} | null>(null);

onMounted(async () => {
  const slug = String(route.params.slug || '');
  try {
    const response = await apiClient.get<
      ApiResponse<{
        slug: string;
        seo_title: string;
        seo_description: string;
        snapshot: {
          project_name: string;
          slug: string;
          content: LandingPageContent;
          theme: LandingPageTheme;
          seo: { title: string; description: string };
        };
      }>
    >(`/pages/${slug}`);

    snapshot.value = response.data.data.snapshot;
    if (response.data.data.seo_title) {
      document.title = response.data.data.seo_title;
    }
  } catch (err) {
    notFoundError.value = extractErrorMessage(
      err,
      'This landing page is not published or does not exist.'
    );
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <div class="min-h-screen bg-brand-bg">
    <!-- Loading State -->
    <div v-if="loading" class="flex min-h-screen items-center justify-center">
      <div class="flex items-center gap-2 text-sm font-medium text-brand-muted">
        <Sparkles class="h-5 w-5 animate-pulse text-brand-primary" />
        <span>Loading published page...</span>
      </div>
    </div>

    <!-- 404 / Unpublished Private Guard State -->
    <div
      v-else-if="notFoundError || !snapshot"
      class="flex min-h-screen flex-col items-center justify-center px-4 text-center"
    >
      <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-brand-error">
        <AlertCircle class="h-6 w-6" />
      </div>
      <h1 class="mt-4 text-2xl font-bold text-brand-text">Page Not Available (404)</h1>
      <p class="mt-2 max-w-md text-sm text-brand-muted">
        {{ notFoundError }}
      </p>
      <RouterLink to="/" class="mt-6">
        <BaseButton variant="primary">Go to LandingFlow AI</BaseButton>
      </RouterLink>
    </div>

    <!-- Published Page View -->
    <LandingPageRenderer
      v-else
      :content="snapshot.content"
      :theme="snapshot.theme"
      :interactive-editor="false"
    />
  </div>
</template>
