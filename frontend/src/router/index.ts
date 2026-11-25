import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import MarketingHomePage from '@/pages/MarketingHomePage.vue';
import LoginPage from '@/pages/LoginPage.vue';
import RegisterPage from '@/pages/RegisterPage.vue';
import ForgotPasswordPage from '@/pages/ForgotPasswordPage.vue';
import DashboardPage from '@/pages/DashboardPage.vue';
import ProjectsPage from '@/pages/ProjectsPage.vue';
import TemplatesPage from '@/pages/TemplatesPage.vue';
import AiGeneratorPage from '@/pages/AiGeneratorPage.vue';
import ProjectEditorPage from '@/pages/ProjectEditorPage.vue';
import PublicLandingPage from '@/pages/PublicLandingPage.vue';
import SettingsPage from '@/pages/SettingsPage.vue';
import HelpPage from '@/pages/HelpPage.vue';

export const routes: RouteRecordRaw[] = [
  {
    path: '/',
    name: 'home',
    component: MarketingHomePage,
    meta: { title: 'LandingFlow AI — AI-Powered Landing Page Builder' },
  },
  {
    path: '/login',
    name: 'login',
    component: LoginPage,
    meta: { guestOnly: true, title: 'Sign In — LandingFlow AI' },
  },
  {
    path: '/register',
    name: 'register',
    component: RegisterPage,
    meta: { guestOnly: true, title: 'Create Account — LandingFlow AI' },
  },
  {
    path: '/forgot-password',
    name: 'forgot-password',
    component: ForgotPasswordPage,
    meta: { guestOnly: true, title: 'Password Recovery — LandingFlow AI' },
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: DashboardPage,
    meta: { requiresAuth: true, title: 'Dashboard' },
  },
  {
    path: '/projects',
    name: 'projects',
    component: ProjectsPage,
    meta: { requiresAuth: true, title: 'My Projects' },
  },
  {
    path: '/projects/:id/edit',
    name: 'project-editor',
    component: ProjectEditorPage,
    meta: { requiresAuth: true, title: 'Landing Page Editor' },
  },
  {
    path: '/templates',
    name: 'templates',
    component: TemplatesPage,
    meta: { requiresAuth: true, title: 'Template Library' },
  },
  {
    path: '/generator',
    name: 'generator',
    component: AiGeneratorPage,
    meta: { requiresAuth: true, title: 'AI Landing Page Generator' },
  },
  {
    path: '/settings',
    name: 'settings',
    component: SettingsPage,
    meta: { requiresAuth: true, title: 'Account Settings' },
  },
  {
    path: '/help',
    name: 'help',
    component: HelpPage,
    meta: { requiresAuth: true, title: 'Help & Architecture' },
  },
  {
    path: '/p/:slug',
    name: 'public-page',
    component: PublicLandingPage,
    meta: { title: 'Published Landing Page' },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to) => {
  const authStore = useAuthStore();
  if (!authStore.initialized) {
    await authStore.fetchCurrentUser();
  }

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return {
      path: '/login',
      query: { redirect: to.fullPath },
    };
  }

  if (to.meta.guestOnly && authStore.isAuthenticated) {
    return { path: '/dashboard' };
  }

  if (typeof to.meta.title === 'string') {
    document.title = `${to.meta.title} | LandingFlow AI`;
  }

  return true;
});

export default router;
