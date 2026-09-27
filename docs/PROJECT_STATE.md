# Campus Coin — Project State

## Current Phase
**Mandatory SRS Gap Completion (IMPLEMENTED & VERIFIED)**
The mandatory code requirements audited for this pass are implemented and tested. The demonstration video and deployment checks remain external deliverables.

## Current Task
The project preserves its existing CampusCoin interface and completes the previously missing mandatory workflows:
- **Account access:** Laravel email reset links use expiring tokens; students can edit their existing profile fields.
- **Ledger:** Monthly recurring entries run through Laravel's scheduler; edit/delete snapshots remain in `transaction_histories` and do not affect totals.
- **Dashboard and administration:** Quick-add reuses transaction validation; administrators can reset student passwords and manage campus tip/announcement templates.
- **Privacy and accessibility:** PDF category labels are user-scoped; small, normal, and large text preferences persist in browser storage alongside the existing dark mode.
- **Automated Regression Suite:** 164 tests, 810 assertions, all passing.
- **Asset Compilation & Code Quality:** Vite production build succeeds; Laravel Pint has formatted changed PHP files.

## Overall Completion
Mandatory implementation is verified. The SRS-required demonstration video remains a manual submission item; hosting and scheduler operation require deployment verification.

## Phase Definitions & Roadmap
- **Phase 0:** Project Initialization, Scaffolding & Multi-Role Authentication (COMPLETED)
- **Phase 1:** Core Student Data & Initial Dashboard (Categories, Transactions, Initial Dashboard KPIs) (COMPLETED)
- **Phase 2:** Budget Goals, Alerts & Dashboard Budget Integration (COMPLETED & VERIFIED)
- **Phase 3:** Interactive Cash Flow Trends & Advanced Analytics (COMPLETED & VERIFIED)
- **Phase 4:** Monthly Financial Reports & Multi-Format Exports (CSV/PDF) (COMPLETED & VERIFIED)
- **Phase 5:** Deterministic Saving Tips Engine & Bookmarks (COMPLETED & VERIFIED)
- **Phase 6:** Advisory AI Categorization & CSV Batch Processing (COMPLETED & VERIFIED)
- **Phase 7:** Operational Admin Panel & Category Controls (COMPLETED & VERIFIED)
- **Phase 8:** Accessibility Controls & Final Hardening (COMPLETED & VERIFIED)
- **Distinctive Product Design:** Tactile Physical Interaction, Warm Ivory & Olive Palette, and Distinctive Geometry (COMPLETED & VERIFIED)

## Completed Features
- **Environment & Framework:** PHP 8.4, Composer 2.x, Node 22, Laravel 13.17, Livewire 4.4, Tailwind CSS v4; local development uses SQLite.
- **Database & Schemas:**
  - `users`: student profile fields (`academic_year`, `monthly_allowance`, `savings_goal`), role separation (`student`, `admin`), and account status (`active`, `disabled`).
  - `categories`: personal and system default categories with icon, hex color, type (`income`, `expense`), and operational status `is_active` (`boolean`, default `true`, indexed).
  - `transactions`: `DECIMAL(10,2)` monetary values, category association, monthly recurrence dates/source IDs, and advisory AI flags (`ai_suggested`, `ai_confidence`).
  - `transaction_histories`: user-owned before-change snapshots for edited and deleted ledger rows.
  - `system_tip_templates`: administrator-managed announcements and tips shown to students when active.
  - `budgets`: `DECIMAL(10,2)` planned spending limits by student, expense category, and `month_year` (`YYYY-MM`) with unique composite key.
  - `saving_tips`: composite unique index `(user_id, rule_key, category_id)` with status tracking (`active`, `dismissed`, `pinned`).
  - `category_learnings`: student-isolated preference mappings `(user_id, keyword)`.
- **Authentication & Authorization:**
  - Multi-role session authentication with CSRF protection.
  - Student registration with `.edu` domain validation and cohort selection.
  - Email password recovery and student-only profile editing.
  - Student login screen with 60/40 asymmetric layout, proof metrics, and `autocomplete` fields.
  - Direct-access administrator login portal (`/admin/login`).
  - Middleware: `EnsureUserIsAdmin` (direct root role guard) and `EnsureUserIsActive` (appended to `web` middleware pipeline, terminating sessions and redirecting/aborting disabled users).
  - Multi-tenant student isolation enforcing `where('user_id', Auth::id())` across all personal data queries.
- **Visual Design & Tactile Experience:**
  - Standardized component classes: `.btn-primary`, `.btn-secondary`, `.btn-icon`, `.input-campus`, `.segmented-bar`, `.segmented-item`, `.card-campus`, `.metric-tile`, `.table-row-tactile`, `.modal-dialog-surface`.
  - Consistent layout mirroring across `resources/views/layouts/` and `resources/views/components/layouts/`.
  - Native SVG charts adopting CSS variables for mode-independent rendering.
- **Accessibility & UX Controls (Phase 8):**
  - **FOUC Prevention & Immediate Boot:** Synchronous `<head>` boot script reads `localStorage` for theme and font-size preferences, immediately applying `.dark` and `data-font-size="..."` prior to render to eliminate layout jumps.
  - **Font-Size Scaling Preference:** A persisted selector offers small (14px), normal (16px), and large (18px) text in guest, student, and admin layouts.
  - **Keyboard Navigation & Visible Focus:** High-contrast focus indicators (`outline: 2px solid var(--accent-primary) !important`) for all interactive elements via `:focus-visible`.
  - **Skip to Main Content:** Accessible skip navigation link on every layout with focus slide-in transition targeting `<main id="main-content" tabindex="-1">`.
  - **Reduced Motion:** Global `@media (prefers-reduced-motion: reduce)` block nullifying transitions, animations, and smooth scrolling for users with vestibular sensitivities.
  - **Semantic Modal Dialogs:** Full ARIA modal attributes across all 8 dialogs in the application.
  - **Accessible Data Tables:** Complete table headers with `<th scope="col">` and dynamic `aria-sort` indicators.
- **Verification Baseline:**
  - Automated Tests: 164 tests, 810 assertions, 100% passing.
  - Linting: Laravel Pint clean.
  - Assets: Vite production build clean.
