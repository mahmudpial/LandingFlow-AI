# LandingFlow-AI

**AI-Powered SaaS Landing Page Studio** built with **Laravel (PHP 8.3+)**, **Vue 3 + TypeScript**, **Tailwind CSS**, and **Amazon Bedrock** (with a zero-cost deterministic `MockAIProvider` for local development and automated testing).

---

## Core Capabilities

- **Stateful Cookie-Based SPA Authentication**: Laravel Sanctum HTTP-only cookie sessions + CSRF protection, registration, login, password reset, email verification, and account settings (zero tokens in `localStorage`).
- **Real-Time SaaS Dashboard**: Live project counts, published page metrics, AI generation usage tracking, recent projects, and audit activity log.
- **Project Management**: Full CRUD, search, status filtering, sorting, project duplication, and strict multi-tenant authorization via Laravel Policies.
- **Multi-Step Copy Architect (AI Generator)**: Generates structured, schema-validated landing pages (Hero, Problem/Solution, Features, Benefits, Social Proof, Illustrative Testimonials, Illustrative Pricing, FAQ, CTA, Footer, and SEO metadata) via `MockAIProvider` or `BedrockProvider` (Amazon Bedrock Converse API).
- **Reusable Layout Systems**: Three handcrafted, responsive templates (*Linear Obsidian SaaS*, *Editorial Vermilion Launch*, and *Meridian Advisory & Studio*) that can be switched non-destructively without losing project copy.
- **Tactile Responsive Editor**: Live preview across Desktop, Tablet, and Mobile viewports, section editing, reordering, visibility toggling, color & typography customization, secure image upload, and 30-step Undo/Redo history stack.
- **Public Page Publishing**: Unique slug generation, ownership verification, one-click publish/unpublish, SEO meta tags, and public rendering at `/p/{slug}`.

---

## Repository Structure

```text
LandingFlow-AI/
├── README.md                 # Project overview & setup guide
├── LICENSE                   # MIT License
├── docker-compose.yml        # Local PostgreSQL container setup
├── backend/                  # Laravel API + Sanctum + AI Providers + PHPUnit tests
├── frontend/                 # Vue 3 + TypeScript + Pinia + Tailwind + Vitest tests
├── infrastructure/           # AWS Lightsail, IAM, S3, Nginx, and deployment scripts
└── .github/                  # GitHub Actions CI pipeline
```

---

## Quick Start (Local Development)

### 1. Prerequisites
- **PHP**: 8.3+ with `pdo_sqlite`, `mbstring`, `openssl`, `curl`
- **Composer**: 2.x
- **Node.js**: 20+ / 24+ and **npm** 10+

### 2. Backend Setup
```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan storage:link
php artisan serve --port=8000
```

### 3. Frontend Setup
```bash
cd frontend
cp .env.example .env
npm install
npm run dev
```

### 4. Automated Tests
```bash
# Backend unit & feature tests
cd backend && php artisan test

# Frontend unit & component tests
cd frontend && npm run test:unit
```
