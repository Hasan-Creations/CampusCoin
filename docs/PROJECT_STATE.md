# Campus Coin — Project State

## Current Phase
**Phase 0 — Project Initialization & Foundation Setup (COMPLETED)**
Transitioning to: **Phase 1 — Core Student Data (Categories & Transactions)**

## Current Task
Phase 0 foundation fully completed and verified. Ready to begin Phase 1: personal category management, system default categories, and transaction ledger CRUD.

## Overall Completion
**12%** (Phase 0 complete; Core Student Data scheduled next).

## Completed Features
- Environment verification: PHP 8.4.23, Composer 2.10.2, Node 22.21.0, NPM 10.9.4, MariaDB 10.4.32 on port 3306.
- Laravel 12 application initialized in root with Livewire 3 (`livewire/livewire ^4.4`).
- MySQL / MariaDB database `campus_coin` connected and configured.
- User schema migrated with student profile fields (`academic_year`, `monthly_allowance`, `savings_goal`), role separation (`student`, `admin`), and account status (`active`, `disabled`).
- User model and factory with decimal precision casting (`decimal:2`) and role helpers (`isAdmin()`, `isStudent()`, `isActive()`).
- Database seeder with Default Admin (`admin@campuscoin.edu`) and Default Student (`alex.rivera@campus.edu`).
- Campus Coin design tokens, typography (`Space Grotesk`, `Inter`, `JetBrains Mono`), and anti-cliché CSS rules in `resources/css/app.css`.
- SVG Lucide-compatible line icon component (`<x-icon name="..." />`).
- Complete layout shells: `<x-layouts.app>`, `<x-layouts.guest>`, and `<x-layouts.admin>`.
- Student login screen with 60/40 asymmetric layout and system proof metrics.
- Direct-access administrator login portal (`/admin/login`).
- Student onboarding/registration screen with academic cohort select and live `.edu` campus domain validator.
- Homepage with required Application Sitemap section (SRS §5.3).
- Authorization middleware: `EnsureUserIsAdmin` and `EnsureUserIsActive`.
- All 8 mandatory persistent project documentation files created in `/docs/` and root `README.md`.
- Automated test suite with 11 tests and 45 assertions covering authentication, authorization, and student fields (100% passing).

## Partially Completed Features
- Initial Student Dashboard (`/dashboard`) and Admin Console (`/admin/dashboard`) rendered with live session data and empty states. Real financial cards await Phase 1 transaction ledger.

## Not Started Features
- Personal & Default Categories CRUD (Phase 1)
- Income & Expense Transactions CRUD with decimal precision (Phase 1)
- Recurring Transactions (Phase 1)
- Livewire 12-Column Dashboard with interactive KPIs & Cash Flow Trends (Phase 2)
- Category Budgets, Consumption Tracking, & Safe-to-Spend Logic (Phase 3)
- Monthly Reports & Multi-Format Export (Phase 4)
- Deterministic Saving Tips Engine & Bookmarks (Phase 5)
- Advisory AI Categorization & Insights with user override (Phase 6)
- Operational Admin Panel user toggle & category controls (Phase 7)
- Accessibility controls & advanced UX (Phase 8)

## Known Bugs
None.

## Known Limitations
- Real monetary and banking integrations are intentionally absent per SRS (strictly manual entry / CSV imports).
- Financial values are stored as `DECIMAL(10,2)` to prevent floating-point inaccuracies.

## Open Questions
- None currently blocking Phase 1.

## Current Database State
- Database `campus_coin` active on MySQL/MariaDB `127.0.0.1:3306`.
- Tables migrated: `users`, `password_reset_tokens`, `sessions`, `cache`, `jobs`.
- Default credentials active:
  - Admin: `admin@campuscoin.edu` / `AdminSecure123!`
  - Student: `alex.rivera@campus.edu` / `StudentSecure123!`

## Current Authentication State
- Multi-role support on single `users` table via `role` column (`student`, `admin`).
- Account status tracking via `status` column (`active`, `disabled`).
- Session-based web authentication with CSRF protection, admin route middleware protection, and login rate limiting.

## Current UI State
- Design specifications strictly aligned with Campus Coin UI/UX rules:
  - Typography: Space Grotesk (headings), Inter (body/UI), JetBrains Mono (financial/dates).
  - Light & dark theme palettes active with client-side theme switcher.
  - Strict anti-cliché rules enforced (no pill buttons, no backdrop blur, no gradients, hairline 1px borders, Lucide line icons).

## Current Test Status
- 11 tests, 45 assertions passing (`php artisan test`).

## Last Completed Work
- Phase 0 foundation: setup, layout components, authentication screens, Vite build validation, and automated feature test verification.

## Immediate Next Task
- **Phase 1 — Core Student Data:**
  - Create `Category` model, migration, seeder with system default categories, and Livewire/Blade management interface.
  - Create `Transaction` model and migration with `DECIMAL(10,2)` amount, category relation, payment method, recurring flag, and date.
  - Implement transaction creation, edit, deletion, and search/filter list.

## Files Recently Changed
- `routes/web.php`
- `app/Http/Controllers/Auth/LoginController.php`
- `app/Http/Controllers/Auth/RegisterController.php`
- `app/Http/Middleware/EnsureUserIsAdmin.php`
- `app/Http/Middleware/EnsureUserIsActive.php`
- `resources/css/app.css`
- `resources/views/components/icon.blade.php`
- `resources/views/components/layouts/app.blade.php`
- `resources/views/components/layouts/guest.blade.php`
- `resources/views/components/layouts/admin.blade.php`
- `resources/views/auth/login.blade.php`
- `resources/views/auth/admin-login.blade.php`
- `resources/views/auth/register.blade.php`
- `resources/views/welcome.blade.php`
- `resources/views/dashboard.blade.php`
- `resources/views/admin/dashboard.blade.php`
- `tests/Feature/CampusCoinAuthTest.php`
- `docs/PROJECT_STATE.md`

## Instructions For The Next Agent
1. Read `/docs/PROJECT_STATE.md`, `/docs/HANDOFF.md`, and `/docs/REQUIREMENTS_TRACEABILITY.md`.
2. Proceed to Phase 1: Category & Transaction models and management interfaces.
3. Every financial calculation must remain deterministic (`DECIMAL(10,2)`).
4. Strictly isolate student data by scoping queries with `where('user_id', Auth::id())`.
5. Keep UI aligned with the Linear/Stripe fintech aesthetic (hairline borders, Space Grotesk headings, Inter body, JetBrains Mono numbers, no pill buttons).
6. Run `php artisan test` after implementing Phase 1 to verify test coverage.
