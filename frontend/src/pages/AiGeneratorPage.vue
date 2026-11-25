<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useProjectStore } from '@/stores/projects';
import { useToastStore } from '@/stores/toast';
import { aiGeneratorWizardSchema } from '@/utils/validation';
import { extractErrorMessage } from '@/services/api';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import BaseInput from '@/components/ui/BaseInput.vue';
import BaseTextarea from '@/components/ui/BaseTextarea.vue';
import BaseBadge from '@/components/ui/BaseBadge.vue';

const router = useRouter();
const projectStore = useProjectStore();
const toastStore = useToastStore();

const currentStep = ref(1);
const targetProjectId = ref<number | 'new'>('new');
const selectedTemplateId = ref<number | undefined>(undefined);
const keywordInput = ref('SaaS automation, workflow, conversion architecture');
const errorMessage = ref('');

const wizard = reactive({
  product_name: '',
  product_description: '',
  industry: 'B2B SaaS & Developer Tools',
  target_audience: 'Founders, Product Engineers, and Growth Teams',
  website_goal: 'Start Free 14-Day Trial',
  brand_tone: 'Modern, clear, and authoritative',
  language: 'English',
  primary_cta: 'Start Free Trial',
});

onMounted(async () => {
  await Promise.all([projectStore.fetchTemplates(), projectStore.fetchProjects()]);
  if (projectStore.templates.length > 0) {
    selectedTemplateId.value = projectStore.templates[0].id;
  }
});

const parsedKeywords = computed(() =>
  keywordInput.value
    .split(',')
    .map((k) => k.trim())
    .filter(Boolean)
    .slice(0, 10)
);

function nextStep(): void {
  errorMessage.value = '';
  if (currentStep.value === 1) {
    if (wizard.product_name.trim().length < 2) {
      errorMessage.value = 'Enter a product or business name (minimum 2 characters).';
      return;
    }
    if (wizard.product_description.trim().length < 10) {
      errorMessage.value = 'Provide a product brief of at least 10 characters.';
      return;
    }
  }
  if (currentStep.value < 3) {
    currentStep.value++;
  }
}

function prevStep(): void {
  errorMessage.value = '';
  if (currentStep.value > 1) {
    currentStep.value--;
  }
}

async function handleGenerate(): Promise<void> {
  errorMessage.value = '';

  const payload = {
    ...wizard,
    keywords: parsedKeywords.value,
  };

  const validation = aiGeneratorWizardSchema.safeParse(payload);
  if (!validation.success) {
    errorMessage.value = validation.error.issues[0]?.message || 'Complete all required fields.';
    return;
  }

  try {
    let projectId: number;
    if (targetProjectId.value === 'new') {
      const created = await projectStore.createProject({
        name: validation.data.product_name,
        description: validation.data.product_description,
        template_id: selectedTemplateId.value,
      });
      projectId = created.id;
    } else {
      projectId = targetProjectId.value;
    }

    const result = await projectStore.generateLandingPage(projectId, validation.data);
    toastStore.success(
      'Conversion schema compiled',
      `Built via ${result.generation.provider} (${result.generation.duration_ms}ms).`
    );
    router.push(`/projects/${projectId}/edit`);
  } catch (err) {
    errorMessage.value = extractErrorMessage(err, 'Copy generation failed. Please try again.');
  }
}
</script>

<template>
  <DashboardLayout>
    <div class="mx-auto max-w-2xl">
      <!-- Header -->
      <div class="mb-5 flex items-center justify-between">
        <div>
          <div class="flex items-center gap-2">
            <h2 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-zinc-100">Copy Architect</h2>
            <BaseBadge variant="neutral">10-Section Schema Compiler</BaseBadge>
          </div>
          <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
            Define your product positioning, audience, and conversion goal to compile a complete page.
          </p>
        </div>
      </div>

      <!-- Segmented Step Header -->
      <div class="mb-5 grid grid-cols-3 divide-x divide-zinc-200 overflow-hidden rounded-lg border border-zinc-200 bg-white dark:divide-zinc-800 dark:border-zinc-800 dark:bg-zinc-900">
        <button
          v-for="stepNum in [1, 2, 3]"
          :key="stepNum"
          type="button"
          class="px-4 py-2.5 text-left transition"
          :class="
            currentStep === stepNum
              ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-950'
              : currentStep > stepNum
                ? 'bg-zinc-50 text-zinc-900 dark:bg-zinc-800/70 dark:text-zinc-200'
                : 'bg-white text-zinc-400 dark:bg-zinc-900 dark:text-zinc-500'
          "
          @click="stepNum < currentStep ? (currentStep = stepNum) : null"
        >
          <div class="font-mono text-[10px] uppercase tracking-wider opacity-75">
            0{{ stepNum }} / {{ currentStep > stepNum ? 'DONE' : 'STEP' }}
          </div>
          <p class="mt-0.5 text-xs font-semibold">
            {{
              stepNum === 1
                ? 'Product Brief'
                : stepNum === 2
                  ? 'Audience & Goal'
                  : 'Voice & Layout'
            }}
          </p>
        </button>
      </div>

      <!-- Main Specification Card -->
      <div class="rounded-lg border border-zinc-200 bg-white p-6 shadow-subtle dark:border-zinc-800 dark:bg-zinc-900">
        <div
          v-if="errorMessage"
          role="alert"
          class="mb-5 rounded-md border border-rose-200 bg-rose-50 px-3.5 py-2.5 text-xs font-medium text-rose-700 dark:border-rose-800 dark:bg-rose-950/60 dark:text-rose-300"
        >
          {{ errorMessage }}
        </div>

        <!-- STEP 1 -->
        <div v-if="currentStep === 1" class="space-y-4">
          <div>
            <label
              for="target-project"
              class="mb-1.5 block text-xs font-medium text-zinc-700 dark:text-zinc-300"
            >
              Target Project
            </label>
            <select
              id="target-project"
              v-model="targetProjectId"
              class="h-8 w-full rounded-md border border-zinc-200 bg-white px-2.5 text-[13px] text-zinc-900 focus:border-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"
            >
              <option value="new">+ Create New Project Automatically</option>
              <option
                v-for="proj in projectStore.projects"
                :key="proj.id"
                :value="proj.id"
              >
                Overwrite Existing Build: {{ proj.name }}
              </option>
            </select>
          </div>

          <BaseInput
            id="wiz-product-name"
            v-model="wizard.product_name"
            label="Product or Company Name"
            placeholder="e.g., PulseMetrics FinOps"
            required
          />

          <BaseTextarea
            id="wiz-product-desc"
            v-model="wizard.product_description"
            label="Product Positioning Brief"
            placeholder="Describe what your product does, the operational bottleneck it eliminates, and its core differentiator..."
            :rows="4"
            required
            hint="10 to 600 characters."
          />

          <BaseInput
            id="wiz-industry"
            v-model="wizard.industry"
            label="Market Category"
            placeholder="e.g., B2B SaaS, Cloud Infrastructure, Product Advisory"
            required
          />
        </div>

        <!-- STEP 2 -->
        <div v-else-if="currentStep === 2" class="space-y-4">
          <BaseInput
            id="wiz-audience"
            v-model="wizard.target_audience"
            label="Primary Buyer / Audience"
            placeholder="e.g., CTOs, Platform Engineers, and VPs of Engineering"
            required
          />

          <BaseInput
            id="wiz-goal"
            v-model="wizard.website_goal"
            label="Conversion Objective"
            placeholder="e.g., Start 14-day trial or schedule technical architecture review"
            required
          />

          <BaseInput
            id="wiz-cta"
            v-model="wizard.primary_cta"
            label="Primary Call-To-Action Label"
            placeholder="e.g., Start Free Trial"
            required
          />

          <BaseInput
            id="wiz-keywords"
            v-model="keywordInput"
            label="SEO Keywords (Comma-Separated)"
            placeholder="e.g., cloud cost attribution, finops automation, kubernetes cost"
          />
        </div>

        <!-- STEP 3 -->
        <div v-else class="space-y-4">
          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <label
                for="wiz-tone"
                class="mb-1.5 block text-xs font-medium text-zinc-700 dark:text-zinc-300"
              >
                Editorial Voice
              </label>
              <select
                id="wiz-tone"
                v-model="wizard.brand_tone"
                class="h-8 w-full rounded-md border border-zinc-200 bg-white px-2.5 text-[13px] text-zinc-900 focus:border-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"
              >
                <option value="Modern, clear, and authoritative">Modern, clear & authoritative</option>
                <option value="Bold, technical, and launch-focused">Bold, technical & launch-focused</option>
                <option value="Consultative, executive, and precise">Consultative & executive</option>
                <option value="Minimal, direct, and product-led">Minimal & product-led</option>
              </select>
            </div>

            <BaseInput
              id="wiz-language"
              v-model="wizard.language"
              label="Output Language"
              placeholder="English"
              required
            />
          </div>

          <div v-if="targetProjectId === 'new'">
            <label
              for="wiz-template"
              class="mb-1.5 block text-xs font-medium text-zinc-700 dark:text-zinc-300"
            >
              Initial Layout System
            </label>
            <select
              id="wiz-template"
              v-model="selectedTemplateId"
              class="h-8 w-full rounded-md border border-zinc-200 bg-white px-2.5 text-[13px] text-zinc-900 focus:border-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"
            >
              <option
                v-for="tpl in projectStore.templates"
                :key="tpl.id"
                :value="tpl.id"
              >
                {{ tpl.name }} — {{ tpl.description }}
              </option>
            </select>
          </div>

          <div class="rounded-md border border-zinc-200 bg-zinc-50 p-3.5 text-xs text-zinc-600 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-300">
            <p class="font-mono text-[10px] font-semibold uppercase tracking-wider text-zinc-900 dark:text-zinc-100">
              Truthfulness & Schema Guardrail
            </p>
            <p class="mt-1 leading-relaxed">
              Generated social proof, customer quotes, and pricing tiers are flagged with
              <code>is_illustrative: true</code> so placeholder examples are never mistaken for
              verified customer endorsements until you edit them in the studio.
            </p>
          </div>
        </div>

        <!-- Footer Controls -->
        <div class="mt-6 flex items-center justify-between border-t border-zinc-100 pt-4 dark:border-zinc-800">
          <BaseButton
            v-if="currentStep > 1"
            variant="outline"
            :disabled="projectStore.generating"
            @click="prevStep"
          >
            ← Back
          </BaseButton>
          <div v-else />

          <BaseButton
            v-if="currentStep < 3"
            variant="primary"
            @click="nextStep"
          >
            Continue →
          </BaseButton>

          <BaseButton
            v-else
            variant="primary"
            size="lg"
            :loading="projectStore.generating"
            @click="handleGenerate"
          >
            Compile 10-Section Landing Page →
          </BaseButton>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>
