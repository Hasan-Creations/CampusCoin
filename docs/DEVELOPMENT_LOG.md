# Campus Coin — Chronological Development Log

## 2026-09-23 — Phase 0: Project Initialization & Foundation Setup

### Implemented
- Scaffolding of Laravel 12 application on PHP 8.4 with Livewire 3 (`livewire/livewire ^4.4`).
- Connection to local MariaDB database `campus_coin` on port 3306.
- Database schema migration for `users` containing student attributes: `academic_year`, `monthly_allowance`, `savings_goal`, system `role` (`student`, `admin`), and account `status` (`active`, `disabled`).
- User model enhancements: `decimal:2` attribute casting, role detection helpers (`isAdmin()`, `isStudent()`, `isActive()`).
- Database seeders with default Administrator (`admin@campuscoin.edu` / `AdminSecure123!`) and Student (`alex.rivera@campus.edu` / `StudentSecure123!`).
- Design system configuration in `resources/css/app.css` using Space Grotesk, Inter, JetBrains Mono, Campus Coin light/dark color tokens, and strict anti-cliché CSS classes (no pill buttons, no backdrop blur, hairline 1px borders).
- Lucide-compatible SVG line icon Blade component (`<x-icon name="..." />`).
- Complete application layout shells:
  - `<x-layouts.app>`: Student layout with top bar, responsive sidebar, breadcrumbs, allowance KPI, theme toggle, and Livewire scripts.
  - `<x-layouts.guest>`: Authentication shell with system health badge and theme toggle.
  - `<x-layouts.admin>`: Operational staff console layout.
- Student login screen with 60/40 asymmetric layout, value proposition, and system proof metrics.
- Direct-access administrator login portal (`/admin/login`).
- Student onboarding/registration screen with academic cohort select and live `.edu` campus domain validator.
- Homepage with required Application Sitemap section (SRS §5.3).
- Authorization middleware: `EnsureUserIsAdmin` (returns 403 on non-admin) and `EnsureUserIsActive` (logs out disabled users).
- Complete persistent documentation files in `/docs/` and root `README.md`.
- Comprehensive automated test suite in `tests/Feature/CampusCoinAuthTest.php`.

### Changed
- Configured `.env` for MySQL connectivity and localized app name to "Campus Coin".
- Adjusted `UserFactory` to produce valid student cohorts and administrator states.
- Corrected external font import sequence in `resources/css/app.css` to eliminate Vite CSS build warnings.

### Verified
- Executed `php -v` (PHP 8.4.23), `composer -V` (Composer 2.10.2), `node -v` (v22.21.0), `npm -v` (10.9.4).
- Executed MySQL MariaDB service check on port 3306 (version `10.4.32-MariaDB`).
- Executed `php artisan migrate:fresh --seed` — successfully migrated users, sessions, cache, and jobs tables, and seeded initial admin and student accounts.
- Executed `npm run build` — compiled all assets in 2.63s with zero errors and zero warnings.
- Executed `php artisan test` — all 11 tests passed with 45 assertions covering welcome page sitemap, login rendering, admin direct portal, student registration with cohort and financial baselines, dashboard access, student 403 restriction from admin console, and disabled user blocking.

### Issues
- Initial root directory required clean state for `composer create-project`; resolved by running directly in workspace.
- Escaped HTML entities in Blade view required exact text assertion in admin test; resolved.

### Next
- **Phase 1 — Core Student Data:**
  - Create `Category` model, migration, default categories seeder, and management view.
  - Create `Transaction` model, migration, and decimal handling for income and expense entries.
  - Implement transaction CRUD with modal, category assignment, payment method, recurring flag, and search/filter list.
