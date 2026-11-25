<script setup lang="ts">
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import BaseBadge from '@/components/ui/BaseBadge.vue';
</script>

<template>
  <DashboardLayout>
    <div class="mx-auto max-w-3xl space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold tracking-tight text-zinc-900">
            Architecture Specifications
          </h2>
          <p class="mt-0.5 text-xs text-zinc-500">
            Technical reference for LandingFlow Studio's schema engine, security, and cloud runtime.
          </p>
        </div>
        <BaseBadge variant="neutral">v1.0.0</BaseBadge>
      </div>

      <!-- Spec Table -->
      <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-subtle">
        <div class="border-b border-zinc-200 px-5 py-3">
          <h3 class="text-[13px] font-semibold text-zinc-900">Runtime Stack</h3>
        </div>
        <div class="divide-y divide-zinc-100 text-xs">
          <div class="grid grid-cols-3 px-5 py-3">
            <span class="font-medium text-zinc-500">Frontend Client</span>
            <span class="col-span-2 font-mono text-zinc-900">Vue 3.5 + TypeScript + Pinia + Tailwind CSS</span>
          </div>
          <div class="grid grid-cols-3 px-5 py-3">
            <span class="font-medium text-zinc-500">API & Session Layer</span>
            <span class="col-span-2 font-mono text-zinc-900">Laravel 13 REST API (/api/v1) + Sanctum Stateful Cookies</span>
          </div>
          <div class="grid grid-cols-3 px-5 py-3">
            <span class="font-medium text-zinc-500">AI Copy Compiler</span>
            <span class="col-span-2 font-mono text-zinc-900">AIProviderInterface (MockAIProvider / Amazon Bedrock Converse)</span>
          </div>
          <div class="grid grid-cols-3 px-5 py-3">
            <span class="font-medium text-zinc-500">Persistence</span>
            <span class="col-span-2 font-mono text-zinc-900">SQLite (Local Studio) / PostgreSQL 16 (Production)</span>
          </div>
        </div>
      </div>

      <!-- Keyboard Shortcuts -->
      <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-subtle">
        <div class="border-b border-zinc-200 px-5 py-3">
          <h3 class="text-[13px] font-semibold text-zinc-900">Studio Shortcuts</h3>
        </div>
        <div class="divide-y divide-zinc-100 text-xs">
          <div class="flex items-center justify-between px-5 py-2.5">
            <span class="text-zinc-600">Open global command palette & project jump</span>
            <kbd class="kbd-shortcut">⌘K</kbd>
          </div>
          <div class="flex items-center justify-between px-5 py-2.5">
            <span class="text-zinc-600">Close active modal or dialog</span>
            <kbd class="kbd-shortcut">ESC</kbd>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>
