<script setup lang="ts">
import { onMounted } from 'vue';
import { useThemeStore } from '@/stores/theme';
import ToastContainer from '@/components/common/ToastContainer.vue';

const themeStore = useThemeStore();

onMounted(() => {
  themeStore.initTheme();
});
</script>

<template>
  <RouterView />
  <ToastContainer />
</template>
