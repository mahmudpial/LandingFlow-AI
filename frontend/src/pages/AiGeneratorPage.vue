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
import { Sparkles, ArrowRight, ArrowLeft, CheckCircle2, Info } from 'lucide-vue-next';

const router = useRouter();
const projectStore = useProjectStore();
const toastStore = useToastStore();

const currentStep = ref(1);
const targetProjectId = ref<number | 'new'>('new');
const selectedTemplateId = ref<number | undefined>(undefined);
const keywordInput = ref('SaaS automation, AI workflow, conversion optimization');
const errorMessage = ref('');

const wizard = reactive({
  product_name: '',
  product_description: '',
  industry: 'B2B SaaS & Software',
  target_audience: 'Startup Founders, Product Managers, and Growth Teams',
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
      errorMessage.value = 'Please enter a product or business name (at least 2 characters).';
      return;
    }
    if (wizard.product_description.trim().length < 10) {
      errorMessage.value = 'Please provide a product description of at least 10 characters.';
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
    errorMessage.value = validation.error.issues[0]?.message || 'Please complete all required wizard fields.';
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
      'AI Landing Page Generated!',
      `Generated via ${result.generation.provider} (${result.generation.duration_ms}ms).`
    );
    router.push(`/projects/${projectId}/edit`);
  } catch (err) {
    errorMessage.value = extractErrorMessage(err, 'AI generation failed. Please try again.');
  }
}
</script>

<template>
  <DashboardLayout>
    <div class="mx-auto max-w-3xl">
      <!-- Wizard Header -->
      <div class="mb-6 flex items-center justify-between">
        <div>
          <div class="flex items-center gap-2">
            <h2 class="text-xl font-bold text-brand-text">AI Landing Page Generator</h2>
            <BaseBadge variant="ai">Structured Schema Engine</BaseBadge>
          </div>
          <p class="mt-1 text-sm text-brand-muted">
            Answer 3 quick steps and our AI engine will craft a complete 10-section landing page.
          </p>
        </div>
      </div>

      <!-- Step Progress Bar -->
      <div class="mb-8 grid grid-cols-3 gap-3">
        <div
          v-for="stepNum in [1, 2, 3]"
          :key="stepNum"
          class="rounded-xl border p-3 transition"
          :class="
            currentStep >= stepNum
              ? 'border-brand-primary bg-indigo-50/60 text-brand-primary'
              : 'border-brand-border bg-white text-slate-400'
          "
        >
          <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider">
            <CheckCircle2 v-if="currentStep > stepNum" class="h-4 w-4 text-brand-success" />
            <span>Step {{ stepNum }}</span>
          </div>
          <p class="mt-1 text-xs font-medium text-slate-700">
            {{
              stepNum === 1
                ? 'Product & Industry'
                : stepNum === 2
                  ? 'Audience & Conversion Goal'
                  : 'Tone, Template & Generate'
            }}
          </p>
        </div>
      </div>

      <!-- Form Card -->
      <div class="rounded-2xl border border-brand-border bg-white p-6 shadow-card sm:p-8">
        <div
          v-if="errorMessage"
          role="alert"
          class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-xs font-medium text-brand-error"
        >
          {{ errorMessage }}
        </div>

        <!-- STEP 1: Product Identity -->
        <div v-if="currentStep === 1" class="space-y-5">
          <div>
            <label
              for="target-project"
              class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-700"
            >
              Destination Project
            </label>
            <select
              id="target-project"
              v-model="targetProjectId"
              class="w-full rounded-lg border border-brand-border bg-white px-3.5 py-2 text-sm text-brand-text"
            >
              <option value="new">+ Create a New Project Automatically</option>
              <option
                v-for="proj in projectStore.projects"
                :key="proj.id"
                :value="proj.id"
              >
                Overwrite Existing Project: {{ proj.name }}
              </option>
            </select>
          </div>

          <BaseInput
            id="wiz-product-name"
            v-model="wizard.product_name"
            label="Product or Business Name"
            placeholder="e.g., PulseMetrics AI"
            required
          />

          <BaseTextarea
            id="wiz-product-desc"
            v-model="wizard.product_description"
            label="Product / Service Description"
            placeholder="Describe what your product does, the core problem it solves, and what makes it unique..."
            :rows="4"
            required
            hint="Minimum 10 characters, up to 600 characters."
          />

          <BaseInput
            id="wiz-industry"
            v-model="wizard.industry"
            label="Industry / Category"
            placeholder="e.g., B2B SaaS, FinTech, Digital Agency, Creator Tools"
            required
          />
        </div>

        <!-- STEP 2: Audience & Conversion Goal -->
        <div v-else-if="currentStep === 2" class="space-y-5">
          <BaseInput
            id="wiz-audience"
            v-model="wizard.target_audience"
            label="Target Audience"
            placeholder="e.g., SaaS Founders, RevOps leaders, and Engineering Managers"
            required
          />

          <BaseInput
            id="wiz-goal"
            v-model="wizard.website_goal"
            label="Primary Website Goal"
            placeholder="e.g., Start a 14-day free trial or book a live demo"
            required
          />

          <BaseInput
            id="wiz-cta"
            v-model="wizard.primary_cta"
            label="Primary Call-To-Action Button Text"
            placeholder="e.g., Start Free Trial"
            required
          />

          <BaseInput
            id="wiz-keywords"
            v-model="keywordInput"
            label="Optional SEO Keywords (Comma-Separated)"
            placeholder="e.g., revenue forecasting, churn alerts, SaaS analytics"
          />
        </div>

        <!-- STEP 3: Brand Tone, Language, Template & Truthfulness Notice -->
        <div v-else class="space-y-5">
          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <label
                for="wiz-tone"
                class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-700"
              >
                Brand Tone
              </label>
              <select
                id="wiz-tone"
                v-model="wizard.brand_tone"
                class="w-full rounded-lg border border-brand-border bg-white px-3.5 py-2 text-sm text-brand-text"
              >
                <option value="Modern, clear, and authoritative">Modern, clear, and authoritative</option>
                <option value="Bold, energetic, and launch-focused">Bold, energetic, and launch-focused</option>
                <option value="Consultative, trustworthy, and executive">Consultative, trustworthy, and executive</option>
                <option value="Friendly, minimal, and approachable">Friendly, minimal, and approachable</option>
              </select>
            </div>

            <BaseInput
              id="wiz-language"
              v-model="wizard.language"
              label="Preferred Language"
              placeholder="English"
              required
            />
          </div>

          <div v-if="targetProjectId === 'new'">
            <label
              for="wiz-template"
              class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-700"
            >
              Design Template
            </label>
            <select
              id="wiz-template"
              v-model="selectedTemplateId"
              class="w-full rounded-lg border border-brand-border bg-white px-3.5 py-2 text-sm text-brand-text"
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

          <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs text-amber-900">
            <div class="flex items-start gap-2.5">
              <Info class="mt-0.5 h-4 w-4 shrink-0 text-amber-700" />
              <div>
                <p class="font-bold">AI Content Integrity & Illustrative Placeholders</p>
                <p class="mt-1 leading-relaxed">
                  LandingFlow AI never fabricates real customer endorsements or company metrics as
                  factual claims. Generated testimonials, partner logos, and pricing tiers are marked
                  as <strong>Illustrative Suggestions</strong> so you can replace them with verified
                  details in the editor before publishing.
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Wizard Footer Actions -->
        <div class="mt-8 flex items-center justify-between border-t border-slate-100 pt-5">
          <BaseButton
            v-if="currentStep > 1"
            variant="outline"
            :disabled="projectStore.generating"
            @click="prevStep"
          >
            <ArrowLeft class="h-4 w-4" />
            <span>Previous</span>
          </BaseButton>
          <div v-else />

          <BaseButton
            v-if="currentStep < 3"
            variant="primary"
            @click="nextStep"
          >
            <span>Continue</span>
            <ArrowRight class="h-4 w-4" />
          </BaseButton>

          <BaseButton
            v-else
            variant="primary"
            size="lg"
            :loading="projectStore.generating"
            @click="handleGenerate"
          >
            <Sparkles class="h-4 w-4" />
            <span>Generate Complete Landing Page</span>
          </BaseButton>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>
