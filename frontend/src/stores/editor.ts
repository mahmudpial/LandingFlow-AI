import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { apiClient, ensureCsrfCookie } from '@/services/api';
import type {
  ApiResponse,
  LandingPageContent,
  LandingPageTheme,
  Project,
  SectionKey,
} from '@/types';

interface EditorSnapshot {
  content: LandingPageContent;
  theme: LandingPageTheme;
  seo_title: string;
  seo_description: string;
}

function deepClone<T>(obj: T): T {
  return JSON.parse(JSON.stringify(obj)) as T;
}

export const useEditorStore = defineStore('editor', () => {
  const projectId = ref<number | null>(null);
  const content = ref<LandingPageContent | null>(null);
  const theme = ref<LandingPageTheme | null>(null);
  const seoTitle = ref('');
  const seoDescription = ref('');
  const activeSection = ref<SectionKey>('hero');
  const viewport = ref<'desktop' | 'tablet' | 'mobile'>('desktop');
  const saving = ref(false);
  const isDirty = ref(false);

  const history = ref<EditorSnapshot[]>([]);
  const historyIndex = ref(-1);

  const canUndo = computed(() => historyIndex.value > 0);
  const canRedo = computed(() => historyIndex.value >= 0 && historyIndex.value < history.value.length - 1);

  function loadFromProject(project: Project): void {
    projectId.value = project.id;
    content.value = deepClone(project.content);
    theme.value = deepClone(project.theme);
    seoTitle.value = project.seo_title || project.content?.seo?.title || project.name;
    seoDescription.value = project.seo_description || project.content?.seo?.description || '';
    activeSection.value = project.content?.sections_order?.[0] || 'hero';
    isDirty.value = false;

    history.value = [
      deepClone({
        content: content.value,
        theme: theme.value,
        seo_title: seoTitle.value,
        seo_description: seoDescription.value,
      }),
    ];
    historyIndex.value = 0;
  }

  function recordSnapshot(): void {
    if (!content.value || !theme.value) return;

    const nextSnapshot = deepClone({
      content: content.value,
      theme: theme.value,
      seo_title: seoTitle.value,
      seo_description: seoDescription.value,
    });

    if (historyIndex.value < history.value.length - 1) {
      history.value = history.value.slice(0, historyIndex.value + 1);
    }

    history.value.push(nextSnapshot);
    if (history.value.length > 30) {
      history.value.shift();
    } else {
      historyIndex.value++;
    }
    isDirty.value = true;
  }

  function undo(): void {
    if (!canUndo.value) return;
    historyIndex.value--;
    const snap = deepClone(history.value[historyIndex.value]);
    content.value = snap.content;
    theme.value = snap.theme;
    seoTitle.value = snap.seo_title;
    seoDescription.value = snap.seo_description;
    isDirty.value = true;
  }

  function redo(): void {
    if (!canRedo.value) return;
    historyIndex.value++;
    const snap = deepClone(history.value[historyIndex.value]);
    content.value = snap.content;
    theme.value = snap.theme;
    seoTitle.value = snap.seo_title;
    seoDescription.value = snap.seo_description;
    isDirty.value = true;
  }

  function toggleSectionVisibility(sectionKey: SectionKey): void {
    if (!content.value) return;
    const section = content.value.sections[sectionKey];
    if (section) {
      section.visible = !section.visible;
      recordSnapshot();
    }
  }

  function moveSection(sectionKey: SectionKey, direction: 'up' | 'down'): void {
    if (!content.value) return;
    const order = [...content.value.sections_order];
    const index = order.indexOf(sectionKey);
    if (index === -1) return;

    const targetIndex = direction === 'up' ? index - 1 : index + 1;
    if (targetIndex < 0 || targetIndex >= order.length) return;

    const [removed] = order.splice(index, 1);
    order.splice(targetIndex, 0, removed);
    content.value.sections_order = order;
    recordSnapshot();
  }

  function updateColor(colorKey: keyof LandingPageTheme['colors'], hexValue: string): void {
    if (!theme.value) return;
    theme.value.colors[colorKey] = hexValue;
    recordSnapshot();
  }

  function updateTypography(field: keyof LandingPageTheme['typography'], value: string): void {
    if (!theme.value) return;
    theme.value.typography[field] = value;
    recordSnapshot();
  }

  async function saveChanges(): Promise<Project | null> {
    if (!projectId.value || !content.value || !theme.value) return null;
    saving.value = true;
    try {
      content.value.seo.title = seoTitle.value;
      content.value.seo.description = seoDescription.value;

      await ensureCsrfCookie();
      const response = await apiClient.put<ApiResponse<Project>>(
        `/projects/${projectId.value}/content`,
        {
          content: content.value,
          theme: theme.value,
          seo_title: seoTitle.value,
          seo_description: seoDescription.value,
        }
      );
      isDirty.value = false;
      return response.data.data;
    } finally {
      saving.value = false;
    }
  }

  return {
    projectId,
    content,
    theme,
    seoTitle,
    seoDescription,
    activeSection,
    viewport,
    saving,
    isDirty,
    canUndo,
    canRedo,
    loadFromProject,
    recordSnapshot,
    undo,
    redo,
    toggleSectionVisibility,
    moveSection,
    updateColor,
    updateTypography,
    saveChanges,
  };
});
