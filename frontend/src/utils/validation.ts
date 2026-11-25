import { z } from 'zod';

export const loginSchema = z.object({
  email: z.string().trim().email('Please enter a valid email address.'),
  password: z.string().min(1, 'Password is required.'),
  remember: z.boolean().optional(),
});

export const registerSchema = z
  .object({
    name: z.string().trim().min(2, 'Name must be at least 2 characters.').max(120),
    email: z.string().trim().email('Please enter a valid email address.'),
    company_name: z.string().trim().max(150).optional(),
    job_title: z.string().trim().max(120).optional(),
    password: z
      .string()
      .min(8, 'Password must be at least 8 characters.')
      .regex(/[A-Za-z]/, 'Password must contain at least one letter.')
      .regex(/[0-9]/, 'Password must contain at least one number.'),
    password_confirmation: z.string(),
  })
  .refine((data) => data.password === data.password_confirmation, {
    message: 'Passwords do not match.',
    path: ['password_confirmation'],
  });

export const createProjectSchema = z.object({
  name: z.string().trim().min(2, 'Project name must be at least 2 characters.').max(160),
  description: z.string().trim().max(600).optional(),
  template_id: z.number().int().positive().optional(),
});

export const aiGeneratorWizardSchema = z.object({
  product_name: z.string().trim().min(2, 'Product or business name is required.').max(120),
  product_description: z
    .string()
    .trim()
    .min(10, 'Please describe your product or service in at least 10 characters.')
    .max(600),
  industry: z.string().trim().min(2, 'Industry is required.').max(100),
  target_audience: z.string().trim().min(3, 'Target audience is required.').max(200),
  website_goal: z.string().trim().min(3, 'Primary website goal is required.').max(150),
  brand_tone: z.string().trim().min(2, 'Brand tone is required.').max(80),
  language: z.string().trim().min(2, 'Language is required.').max(50),
  primary_cta: z.string().trim().min(2, 'Primary call to action text is required.').max(60),
  keywords: z.array(z.string().trim().max(50)).max(10).default([]),
});

/**
 * Safe URL sanitizer for rendering CTA links in landing page sections.
 * Blocks javascript:, data:, and vbscript: URIs.
 */
export function sanitizeHref(rawUrl: string | undefined | null, fallback = '#'): string {
  if (!rawUrl || !rawUrl.trim()) {
    return fallback;
  }
  const trimmed = rawUrl.trim();
  if (trimmed.startsWith('#') || trimmed.startsWith('/')) {
    return trimmed;
  }
  if (/^(https?:\/\/|mailto:|tel:)/i.test(trimmed)) {
    return trimmed;
  }
  return fallback;
}
