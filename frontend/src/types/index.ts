export interface ApiResponse<T> {
  success: boolean;
  message: string;
  data: T;
  meta?: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
  errors?: Record<string, string[]>;
}

export interface User {
  id: number;
  uuid: string;
  name: string;
  email: string;
  company_name: string | null;
  job_title: string | null;
  email_verified: boolean;
  email_verified_at: string | null;
  ai_generations_count: number;
  created_at: string;
}

export type SectionKey =
  | 'hero'
  | 'problem_solution'
  | 'features'
  | 'benefits'
  | 'social_proof'
  | 'testimonials'
  | 'pricing'
  | 'faq'
  | 'cta'
  | 'footer';

export interface HeroSectionData {
  visible: boolean;
  badge: string;
  headline: string;
  subheadline: string;
  primary_cta_text: string;
  primary_cta_url: string;
  secondary_cta_text: string;
  secondary_cta_url: string;
  image_url: string;
  highlights: string[];
}

export interface ProblemSolutionSectionData {
  visible: boolean;
  title: string;
  subtitle: string;
  problem_title: string;
  problem_items: string[];
  solution_title: string;
  solution_items: string[];
}

export interface FeatureItem {
  icon: string;
  title: string;
  description: string;
}

export interface FeaturesSectionData {
  visible: boolean;
  title: string;
  subtitle: string;
  items: FeatureItem[];
}

export interface BenefitItem {
  title: string;
  description: string;
  metric_label: string;
}

export interface BenefitsSectionData {
  visible: boolean;
  title: string;
  subtitle: string;
  items: BenefitItem[];
}

export interface SocialProofSectionData {
  visible: boolean;
  is_illustrative: boolean;
  disclaimer: string;
  title: string;
  logos: string[];
}

export interface TestimonialItem {
  quote: string;
  author_name: string;
  author_role: string;
  company: string;
  is_illustrative?: boolean;
}

export interface TestimonialsSectionData {
  visible: boolean;
  is_illustrative: boolean;
  disclaimer: string;
  title: string;
  subtitle: string;
  items: TestimonialItem[];
}

export interface PricingPlan {
  name: string;
  price: string;
  period: string;
  description: string;
  features: string[];
  cta_text: string;
  cta_url: string;
  is_popular: boolean;
}

export interface PricingSectionData {
  visible: boolean;
  is_illustrative: boolean;
  disclaimer: string;
  title: string;
  subtitle: string;
  plans: PricingPlan[];
}

export interface FaqItem {
  question: string;
  answer: string;
}

export interface FaqSectionData {
  visible: boolean;
  title: string;
  subtitle: string;
  items: FaqItem[];
}

export interface CtaSectionData {
  visible: boolean;
  headline: string;
  subheadline: string;
  button_text: string;
  button_url: string;
}

export interface FooterLink {
  label: string;
  url: string;
}

export interface FooterSectionData {
  visible: boolean;
  brand_name: string;
  tagline: string;
  copyright_text: string;
  links: FooterLink[];
}

export interface LandingPageSections {
  hero: HeroSectionData;
  problem_solution: ProblemSolutionSectionData;
  features: FeaturesSectionData;
  benefits: BenefitsSectionData;
  social_proof: SocialProofSectionData;
  testimonials: TestimonialsSectionData;
  pricing: PricingSectionData;
  faq: FaqSectionData;
  cta: CtaSectionData;
  footer: FooterSectionData;
}

export interface LandingPageSeo {
  title: string;
  description: string;
  keywords: string[];
}

export interface LandingPageContent {
  seo: LandingPageSeo;
  sections_order: SectionKey[];
  sections: LandingPageSections;
}

export interface ColorPalette {
  primary: string;
  secondary: string;
  accent: string;
  background: string;
  surface: string;
  text: string;
}

export interface TypographyConfig {
  heading_font: string;
  body_font: string;
  scale: string;
}

export interface LandingPageTheme {
  template_slug: string;
  layout_variant: string;
  colors: ColorPalette;
  typography: TypographyConfig;
}

export interface Template {
  id: number;
  slug: string;
  name: string;
  description: string;
  category: 'saas' | 'product' | 'agency' | string;
  preview_image_url: string | null;
  color_palette: ColorPalette;
  typography: TypographyConfig;
  supported_sections: SectionKey[];
  default_schema: LandingPageContent;
  is_active: boolean;
}

export interface WizardBusinessInfo {
  product_name: string;
  product_description: string;
  industry: string;
  target_audience: string;
  website_goal: string;
  brand_tone: string;
  language: string;
  primary_cta: string;
  keywords: string[];
}

export interface GenerationRecord {
  id: number;
  uuid: string;
  project_id: number;
  provider: string;
  model_id: string;
  status: 'pending' | 'completed' | 'failed';
  input_payload: WizardBusinessInfo;
  output_payload?: LandingPageContent;
  error_message?: string | null;
  prompt_tokens: number;
  completion_tokens: number;
  duration_ms: number;
  created_at: string;
}

export interface Project {
  id: number;
  uuid: string;
  user_id: number;
  template_id: number | null;
  name: string;
  slug: string;
  description: string | null;
  status: 'draft' | 'published' | 'archived';
  business_info: WizardBusinessInfo | null;
  content: LandingPageContent;
  theme: LandingPageTheme;
  seo_title: string | null;
  seo_description: string | null;
  public_url: string;
  published_at: string | null;
  template?: Template;
  published_page?: {
    id: number;
    slug: string;
    is_published: boolean;
    view_count: number;
    published_at: string;
  } | null;
  generations?: GenerationRecord[];
  created_at: string;
  updated_at: string;
}

export interface DashboardStats {
  total_projects: number;
  published_pages: number;
  draft_projects: number;
  ai_generations_used: number;
  ai_generations_last_hour: number;
  ai_hourly_limit: number;
  ai_provider: string;
  total_public_views: number;
}

export interface ActivityLogItem {
  id: number;
  action: string;
  description: string;
  project_id: number | null;
  metadata?: Record<string, unknown> | null;
  created_at: string;
}

export interface MediaAsset {
  id: number;
  uuid: string;
  project_id: number | null;
  original_name: string;
  mime_type: string;
  size_bytes: number;
  width: number | null;
  height: number | null;
  url: string;
  created_at: string;
}
