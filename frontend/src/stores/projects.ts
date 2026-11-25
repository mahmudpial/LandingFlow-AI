import { defineStore } from 'pinia';
import { ref } from 'vue';
import { apiClient, ensureCsrfCookie } from '@/services/api';
import type {
  ActivityLogItem,
  ApiResponse,
  DashboardStats,
  GenerationRecord,
  Project,
  Template,
  WizardBusinessInfo,
} from '@/types';

export const useProjectStore = defineStore('projects', () => {
  const projects = ref<Project[]>([]);
  const currentProject = ref<Project | null>(null);
  const templates = ref<Template[]>([]);
  const dashboardStats = ref<DashboardStats | null>(null);
  const recentProjects = ref<Project[]>([]);
  const recentActivity = ref<ActivityLogItem[]>([]);
  const loading = ref(false);
  const generating = ref(false);
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 12,
    total: 0,
  });

  async function fetchDashboard(): Promise<void> {
    loading.value = true;
    try {
      const response = await apiClient.get<
        ApiResponse<{
          stats: DashboardStats;
          recent_projects: Project[];
          recent_activity: ActivityLogItem[];
        }>
      >('/dashboard');
      dashboardStats.value = response.data.data.stats;
      recentProjects.value = response.data.data.recent_projects;
      recentActivity.value = response.data.data.recent_activity;
    } finally {
      loading.value = false;
    }
  }

  async function fetchTemplates(): Promise<Template[]> {
    if (templates.value.length > 0) {
      return templates.value;
    }
    const response = await apiClient.get<ApiResponse<Template[]>>('/templates');
    templates.value = response.data.data;
    return templates.value;
  }

  async function fetchProjects(params?: {
    search?: string;
    status?: string;
    sort?: string;
    page?: number;
  }): Promise<void> {
    loading.value = true;
    try {
      const response = await apiClient.get<ApiResponse<Project[]>>('/projects', {
        params,
      });
      projects.value = response.data.data;
      if (response.data.meta) {
        pagination.value = response.data.meta;
      }
    } finally {
      loading.value = false;
    }
  }

  async function fetchProject(id: number | string): Promise<Project> {
    loading.value = true;
    try {
      const response = await apiClient.get<ApiResponse<Project>>(`/projects/${id}`);
      currentProject.value = response.data.data;
      return response.data.data;
    } finally {
      loading.value = false;
    }
  }

  async function createProject(payload: {
    name: string;
    description?: string;
    template_id?: number;
  }): Promise<Project> {
    await ensureCsrfCookie();
    const response = await apiClient.post<ApiResponse<Project>>('/projects', payload);
    const created = response.data.data;
    projects.value.unshift(created);
    currentProject.value = created;
    return created;
  }

  async function updateProject(
    id: number,
    payload: Partial<Pick<Project, 'name' | 'description' | 'slug' | 'status' | 'template_id' | 'seo_title' | 'seo_description'>>
  ): Promise<Project> {
    await ensureCsrfCookie();
    const response = await apiClient.put<ApiResponse<Project>>(`/projects/${id}`, payload);
    const updated = response.data.data;
    currentProject.value = updated;
    projects.value = projects.value.map((p) => (p.id === id ? updated : p));
    return updated;
  }

  async function duplicateProject(id: number): Promise<Project> {
    await ensureCsrfCookie();
    const response = await apiClient.post<ApiResponse<Project>>(`/projects/${id}/duplicate`);
    const copy = response.data.data;
    projects.value.unshift(copy);
    return copy;
  }

  async function deleteProject(id: number): Promise<void> {
    await ensureCsrfCookie();
    await apiClient.delete(`/projects/${id}`);
    projects.value = projects.value.filter((p) => p.id !== id);
    if (currentProject.value?.id === id) {
      currentProject.value = null;
    }
  }

  async function generateLandingPage(
    projectId: number,
    wizardInput: WizardBusinessInfo
  ): Promise<{ generation: GenerationRecord; project: Project }> {
    generating.value = true;
    try {
      await ensureCsrfCookie();
      const response = await apiClient.post<
        ApiResponse<{ generation: GenerationRecord; project: Project }>
      >(`/projects/${projectId}/generate`, wizardInput);
      currentProject.value = response.data.data.project;
      projects.value = projects.value.map((p) =>
        p.id === projectId ? response.data.data.project : p
      );
      return response.data.data;
    } finally {
      generating.value = false;
    }
  }

  async function applyTemplate(
    projectId: number,
    templateId: number,
    replaceContent = false
  ): Promise<Project> {
    await ensureCsrfCookie();
    const response = await apiClient.post<ApiResponse<Project>>(
      `/projects/${projectId}/apply-template`,
      {
        template_id: templateId,
        replace_content: replaceContent,
      }
    );
    const updated = response.data.data;
    currentProject.value = updated;
    projects.value = projects.value.map((p) => (p.id === projectId ? updated : p));
    return updated;
  }

  async function publishProject(
    projectId: number,
    publish: boolean,
    slug?: string
  ): Promise<Project> {
    await ensureCsrfCookie();
    const response = await apiClient.post<ApiResponse<Project>>(
      `/projects/${projectId}/publish`,
      {
        publish,
        slug,
      }
    );
    const updated = response.data.data;
    currentProject.value = updated;
    projects.value = projects.value.map((p) => (p.id === projectId ? updated : p));
    return updated;
  }

  return {
    projects,
    currentProject,
    templates,
    dashboardStats,
    recentProjects,
    recentActivity,
    loading,
    generating,
    pagination,
    fetchDashboard,
    fetchTemplates,
    fetchProjects,
    fetchProject,
    createProject,
    updateProject,
    duplicateProject,
    deleteProject,
    generateLandingPage,
    applyTemplate,
    publishProject,
  };
});
