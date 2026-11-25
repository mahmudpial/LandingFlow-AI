import { describe, it, expect, beforeEach } from 'vitest';
import { setActivePinia, createPinia } from 'pinia';
import { mount } from '@vue/test-utils';
import { useEditorStore } from '@/stores/editor';
import {
  sanitizeHref,
  loginSchema,
  registerSchema,
  aiGeneratorWizardSchema,
} from '@/utils/validation';
import LandingPageRenderer from '@/components/landing/LandingPageRenderer.vue';
import type { LandingPageContent, LandingPageTheme, Project } from '@/types';

const sampleContent: LandingPageContent = {
  seo: {
    title: 'CloudPulse Analytics — AI Cloud FinOps',
    description: 'Automated cloud cost observability.',
    keywords: ['cloud', 'finops'],
  },
  sections_order: [
    'hero',
    'problem_solution',
    'features',
    'benefits',
    'social_proof',
    'testimonials',
    'pricing',
    'faq',
    'cta',
    'footer',
  ],
  sections: {
    hero: {
      visible: true,
      badge: 'Cloud FinOps Platform',
      headline: 'Cut Cloud Waste by 40% With Automated Observability',
      subheadline: 'Built for engineering and finance teams.',
      primary_cta_text: 'Start Free Trial',
      primary_cta_url: 'javascript:alert(1)',
      secondary_cta_text: 'Learn More',
      secondary_cta_url: '#features',
      image_url: '',
      highlights: ['5-minute setup', 'SOC2 ready'],
    },
    problem_solution: {
      visible: true,
      title: 'Why Legacy Billing Dashboards Fail',
      subtitle: 'Move from monthly bill shock to real-time alerts.',
      problem_title: 'Before CloudPulse',
      problem_items: ['Manual CSV exports'],
      solution_title: 'With CloudPulse',
      solution_items: ['Real-time anomaly detection'],
    },
    features: {
      visible: true,
      title: 'Everything You Need',
      subtitle: 'Built for scale.',
      items: [
        {
          icon: 'Sparkles',
          title: 'Automated Tagging',
          description: 'Attribute every dollar to a service.',
        },
      ],
    },
    benefits: {
      visible: true,
      title: 'Measurable Impact',
      subtitle: 'Deliver value immediately.',
      items: [
        {
          title: 'Faster Forecasting',
          description: 'Predict spend accurately.',
          metric_label: 'Workflow Gain',
        },
      ],
    },
    social_proof: {
      visible: true,
      is_illustrative: true,
      disclaimer: 'Illustrative placeholder logos.',
      title: 'Trusted by Modern Teams (Sample)',
      logos: ['AcmeCloud (Sample)'],
    },
    testimonials: {
      visible: true,
      is_illustrative: true,
      disclaimer: 'Illustrative sample testimonials — replace with real quotes.',
      title: 'Customer Feedback (Illustrative)',
      subtitle: 'Sample cards',
      items: [
        {
          quote: '[Sample Quote] Great workflow tool.',
          author_name: 'Jordan Taylor (Sample)',
          author_role: 'CTO',
          company: 'Demo Corp',
          is_illustrative: true,
        },
      ],
    },
    pricing: {
      visible: true,
      is_illustrative: true,
      disclaimer: 'Illustrative pricing suggestions.',
      title: 'Simple Pricing (Illustrative)',
      subtitle: 'Sample tiers',
      plans: [
        {
          name: 'Growth',
          price: '$49',
          period: '/month',
          description: 'For growing teams',
          features: ['Unlimited alerts'],
          cta_text: 'Start Trial',
          cta_url: '#cta',
          is_popular: true,
        },
      ],
    },
    faq: {
      visible: true,
      title: 'FAQ',
      subtitle: 'Common questions',
      items: [
        {
          question: 'How fast is onboarding?',
          answer: 'Under 10 minutes.',
        },
      ],
    },
    cta: {
      visible: true,
      headline: 'Ready to Optimize?',
      subheadline: 'Start today.',
      button_text: 'Get Started',
      button_url: 'https://example.com/start',
    },
    footer: {
      visible: true,
      brand_name: 'CloudPulse',
      tagline: 'FinOps simplified.',
      copyright_text: '© 2026 CloudPulse',
      links: [{ label: 'Features', url: '#features' }],
    },
  },
};

const sampleTheme: LandingPageTheme = {
  template_slug: 'startup-saas',
  layout_variant: 'saas',
  colors: {
    primary: '#635BFF',
    secondary: '#8B5CF6',
    accent: '#14B8A6',
    background: '#F8FAFC',
    surface: '#FFFFFF',
    text: '#0F172A',
  },
  typography: {
    heading_font: 'Inter',
    body_font: 'Inter',
    scale: 'modern',
  },
};

const sampleProject: Project = {
  id: 1,
  uuid: '123e4567-e89b-12d3-a456-426614174000',
  user_id: 1,
  template_id: 1,
  name: 'CloudPulse Analytics',
  slug: 'cloudpulse-analytics',
  description: 'FinOps SaaS',
  status: 'draft',
  business_info: null,
  content: sampleContent,
  theme: sampleTheme,
  seo_title: 'CloudPulse Analytics',
  seo_description: 'FinOps SaaS',
  public_url: '/p/cloudpulse-analytics',
  published_at: null,
  created_at: '2026-11-25T00:00:00Z',
  updated_at: '2026-11-25T00:00:00Z',
};

describe('LandingFlow AI Frontend Suite', () => {
  beforeEach(() => {
    setActivePinia(createPinia());
  });

  it('sanitizes malicious javascript: and data: URLs while allowing safe https and anchor links', () => {
    expect(sanitizeHref('javascript:alert(document.cookie)', '#pricing')).toBe('#pricing');
    expect(sanitizeHref('data:text/html,<script>alert(1)</script>', '#')).toBe('#');
    expect(sanitizeHref('https://landingflow.ai/signup')).toBe('https://landingflow.ai/signup');
    expect(sanitizeHref('#features')).toBe('#features');
  });

  it('validates login, registration, and AI wizard Zod schemas accurately', () => {
    expect(loginSchema.safeParse({ email: 'bad-email', password: '' }).success).toBe(false);
    expect(
      loginSchema.safeParse({ email: 'founder@landingflow.ai', password: 'Password123' }).success
    ).toBe(true);

    expect(
      registerSchema.safeParse({
        name: 'Elena',
        email: 'elena@landingflow.ai',
        password: 'Password123',
        password_confirmation: 'Mismatch123',
      }).success
    ).toBe(false);

    expect(
      aiGeneratorWizardSchema.safeParse({
        product_name: 'CloudPulse',
        product_description: 'Automated cloud cost observability and FinOps recommendations.',
        industry: 'Cloud SaaS',
        target_audience: 'DevOps and Engineering Leaders',
        website_goal: 'Start Free Trial',
        brand_tone: 'Modern',
        language: 'English',
        primary_cta: 'Start Trial',
        keywords: ['finops', 'cloud'],
      }).success
    ).toBe(true);
  });

  it('renders LandingPageRenderer with all 10 sections, illustrative disclaimers, and sanitized links', () => {
    const wrapper = mount(LandingPageRenderer, {
      props: {
        content: sampleContent,
        theme: sampleTheme,
      },
    });

    expect(wrapper.text()).toContain('Cut Cloud Waste by 40% With Automated Observability');
    expect(wrapper.text()).toContain('Illustrative sample testimonials — replace with real quotes.');
    expect(wrapper.text()).toContain('Illustrative pricing suggestions.');

    // Verify javascript: URL on primary CTA was neutralized to #pricing
    const heroPrimaryLink = wrapper.find('#hero a');
    expect(heroPrimaryLink.attributes('href')).toBe('#pricing');
  });

  it('supports section reordering, visibility toggling, color updates, and undo/redo in useEditorStore', () => {
    const store = useEditorStore();
    store.loadFromProject(sampleProject);

    expect(store.canUndo).toBe(false);
    expect(store.content?.sections_order[0]).toBe('hero');

    // Move 'features' up from index 2 to index 1
    store.moveSection('features', 'up');
    expect(store.content?.sections_order[1]).toBe('features');
    expect(store.canUndo).toBe(true);

    // Toggle FAQ visibility off
    store.toggleSectionVisibility('faq');
    expect(store.content?.sections.faq.visible).toBe(false);

    // Update primary color
    store.updateColor('primary', '#10B981');
    expect(store.theme?.colors.primary).toBe('#10B981');

    // Undo color change
    store.undo();
    expect(store.theme?.colors.primary).toBe('#635BFF');

    // Redo color change
    store.redo();
    expect(store.theme?.colors.primary).toBe('#10B981');
  });
});
