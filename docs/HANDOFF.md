# Campus Coin — Developer & AI Agent Handoff Guide

> "If I know nothing about this project and open this repository right now, what do I need to know before touching the code?"

## 1. Technology Stack
- **Framework:** Laravel 12 (PHP 8.4+)
- **Dynamic Frontend:** Laravel Livewire 3 + Blade
- **Styling:** Custom CSS design system + Tailwind CSS (configured strictly to Campus Coin tokens)
- **Database:** MySQL / MariaDB (Database: `campus_coin`)
- **Asset Bundler:** Vite
- **Developer Acceleration:** Laravel Boost

---

## 2. Quickstart & Installation
```powershell
# 1. Install Composer dependencies
composer install

# 2. Configure environment
cp .env.example .env
php artisan key:generate

# 3. Migrate and seed database
php artisan migrate:fresh --seed

# 4. Install and build frontend assets
npm install
npm run build

# 5. Run local development server
php artisan serve
```

---

## 3. Database & Default Accounts
- **Database Name:** `campus_coin`
- **Host/Port:** `127.0.0.1:3306`
- **Default Administrator:**
  - Email: `admin@campuscoin.edu`
  - Password: `AdminSecure123!`
  - Role: `admin`
- **Default Student 1:**
  - Email: `alex.rivera@campus.edu`
  - Password: `StudentSecure123!`
  - Role: `student`
  - Cohort: `Junior`
  - Monthly Allowance: `$1,200.00`
  - Target Savings Goal: `$300.00`
- **Default Student 2 (Multi-Tenant Isolation Testing):**
  - Email: `maria.santos@campus.edu`
  - Password: `StudentSecure123!`
  - Role: `student`
  - Cohort: `Sophomore`
  - Monthly Allowance: `$950.00`
  - Target Savings Goal: `$200.00`

---

## 4. Crucial Business & Architectural Rules
1. **Never use floats for money:** All monetary numbers must be `DECIMAL(10,2)` in database and string/BCMath in business logic.
2. **Strict Data Isolation:** Never query transactions, budgets, or categories without scoping to `where('user_id', Auth::id())`. A student must never see another student's data.
3. **Ledger-Calculated Budget Consumption:** Do not duplicate or cache transaction spending inside the `budgets` table; compute it dynamically from actual expense transactions.
4. **Expense-Only Budgets:** Budget goals are strictly restricted to `expense` categories.
5. **No Design Anti-Patterns:**
   - No pill buttons (no `rounded-full` or 9999px radius).
   - No glassmorphism, blur, or purple/pink gradients.
   - Use clean 1px hairline borders (`#E2E8F0` light, `#27272A` dark).
   - Fonts: Headings (`Space Grotesk`), UI/Body (`Inter`), Figures/Dates (`JetBrains Mono`).
6. **AI is strictly advisory:** Never force or automatically apply AI suggestions without student confirmation. Core expense tracking must work even if AI is disabled.
7. **Accessibility & Usability First:**
   - Every layout must maintain a high-contrast skip-to-content link pointing to `<main id="main-content" tabindex="-1">`.
   - All interactive elements must maintain visible `:focus-visible` styling.
   - Modals must be semantic dialogs (`role="dialog"`, `aria-modal="true"`, `aria-labelledby`, Escape dismiss).
   - All tables must declare `<th scope="col">` headers.
   - Reduced-motion preferences must be honored via CSS media queries.

---

## 5. Current Implementation Status
- **Phase 0 (Foundation):** COMPLETED & VERIFIED.
  - Laravel 12 + Livewire 3 + MySQL operational.
  - User model, schema, and seeders active.
  - Authentication flow (Student login, Register with cohort/.edu, direct Admin access, logout) active.
- **Phase 1 (Core Student Data & Initial Dashboard):** COMPLETED & VERIFIED.
  - Category model, migration, default seeder, and Livewire `CategoryManager` CRUD.
  - Transaction model, migration, and Livewire `TransactionList` CRUD with CSV export.
  - Initial Dashboard Livewire component with real-time financial KPIs.
- **Phase 2 (Budget Goals & Alerts):** COMPLETED & VERIFIED.
  - `budgets` table with foreign keys and unique constraint on `(user_id, category_id, month_year)`.
  - `Budget` model with BCMath calculations, threshold detection, and status helpers.
  - `BudgetManager` Livewire component (`/budgets`) with monthly filtering, 4 KPI summary cards, progress bars, and alert banners.
  - Dashboard integration: "Budget Goals & Spending Caps" widget and real-time alert banners.
- **Phase 3 (Cash Flow Trends & Advanced Analytics):** COMPLETED & VERIFIED.
  - `FinancialCalculationService` single source of truth for 6-month cash flow and multi-period category comparisons.
  - Responsive native SVG 6-month dual-bar chart showing monthly Income and Expenses with hairline grid lines, baseline, and current month highlight.
  - Interactive multi-period switcher (`This Month`, `3 Months`, `6 Months`, `This Year`) updating Livewire state without full-page reloads.
  - Comparative category spending breakdown with absolute dollar deltas, safe percentage changes, "New" badges, and period share visual progress bars.
- **Phase 4 (Monthly Reports & Financial Reporting):** COMPLETED & VERIFIED.
  - Full-page Reports Livewire component at `/reports` with reactive period presets (`this_month`, `last_month`, `last_3_months`, `last_6_months`, `year`, `custom`).
  - Executive financial summary with Total Inflow, Total Outflow, Net Movement, Savings Rate (%), and prior equal-duration period comparison.
  - Category-wise spending breakdown with expense shares, count, average amount, and percentage changes vs. prior period.
  - 6-month comparative income vs. expense table and current month daily/weekly velocity analysis.
  - Multi-view tabbed navigation (`monthly`, `six_month`, `daily`, `weekly`, `ledger`) and reactive category/type filtering.
  - Server-side PDF export with Dompdf (`/reports/export/pdf`), print-ready layout preview (`?preview=1`), and streaming CSV export (`/reports/export/csv`).
- **Phase 5 (Saving Tips Engine & Intelligent Rule Evaluator):** COMPLETED & VERIFIED.
  - Deterministic `SavingTipsService` evaluating 5 financial rules on student ledger data.
  - BCMath decimal calculations for estimated potential savings figures.
  - Deterministic ranking of tips by potential savings impact descending.
  - `saving_tips` table with composite unique constraint `(user_id, rule_key, category_id)` preventing duplicates and preserving student `pinned` and `dismissed` states.
  - Dedicated student hub at `/tips` (`SavingTipsManager`) with segmented tabs (`active`, `pinned`, `dismissed`), potential savings metrics, and pin/dismiss/restore actions.
  - Dashboard integration: "Personalized Saving Opportunities" widget with top 3 tips and inline pin/dismiss controls.
- **Phase 6 (Advisory AI Categorization Assistant & CSV Batch Processing):** COMPLETED & VERIFIED.
  - Pluggable provider architecture (`CategorizationProviderInterface`) registered in `AppServiceProvider`.
  - Semantic heuristic matching engine (`HeuristicCategorizationProvider`) providing zero-latency fallback across all standard student spending categories.
  - OpenAI provider integration (`OpenAiCategorizationProvider`) with 3s timeout, structured schema prompt, invalid/hallucinated category rejection, and automatic fallback.
  - Student learning and correction persistence (`category_learnings` table) with strict student multi-tenant isolation.
  - Live advisory suggestions during single transaction entry/edit in `TransactionList.php` with one-click acceptance and manual override guarantee.
  - Interactive CSV batch categorization and import modal: parses CSV files up to 50 rows, runs batch suggestions, renders review table with category dropdown overrides, and confirms batch ledger imports.
- **Phase 7 (Operational Admin Panel & Category Controls):** COMPLETED & VERIFIED.
  - Multi-tier administrative authorization: `EnsureUserIsAdmin` middleware, `EnsureUserIsActive` registered in global web pipeline, and server-side authorization in all admin Livewire components.
  - Operational Admin Dashboard (`/admin/dashboard`, `Dashboard.php`) presenting 4 primary telemetry cards, SRS-mandated Most-Used Categories leaderboard, cohort distribution, and recent student account actions.
  - Global Category Governance (`/admin/categories`, `CategoryManager.php`): create global defaults, edit title/type/icon/color, toggle operational status (`is_active`), and safe deletion barrier blocking destructive deletion of referenced categories.
  - Student Account Status Governance (`/admin/users`, `UserManager.php`): status toggling (active/disabled), automated termination of active database sessions on deactivation, inspection drawer, and financial baseline reset.
  - Platform Telemetry Engine (`AdminMetricsService`): high-performance SQL aggregate calculations with zero-state safety and privacy preservation.
- **Phase 8 (Accessibility Controls & Final Hardening):** COMPLETED & VERIFIED.
  - Synchronous `<head>` boot script preventing FOUC and font-size shift.
  - Three-tier root font-size scaling preference (`normal` 100%, `large` 112.5%, `xlarge` 125%) per SRS §1.6 & §185 with header selectors across all layouts.
  - System-wide high-contrast `:focus-visible` indicators and skip-to-content links.
  - Semantic modal dialogs (`role="dialog"`, `aria-modal="true"`, `aria-labelledby`, Escape dismiss) across all modals.
  - Accessible data table column scopes (`<th scope="col">`) and sorting announcements (`aria-sort`).
  - Screen reader announcements (`role="status"`, `role="alert"`) and form input `autocomplete` attributes.
  - Reduced-motion support (`prefers-reduced-motion: reduce`).
  - 151 automated tests (722 assertions) passing at 100%.

---

## 6. Tests to Run
```powershell
# Run full integrated test suite (151 tests / 722 assertions)
php artisan test --compact

# Run accessibility & hardening test suite (12 tests / 74 assertions)
php artisan test tests/Feature/AccessibilityAndHardeningTest.php

# Run admin management test suite (28 tests / 107 assertions)
php artisan test tests/Feature/AdminManagementTest.php

# Run saving tips test suite (19 tests / 82 assertions)
php artisan test tests/Feature/SavingTipsTest.php

# Run AI categorization test suite (15 tests / 70 assertions)
php artisan test tests/Feature/AiCategorizationTest.php

# Run code styling check
php vendor/bin/pint --dirty --format agent

# Build frontend assets
npm run build
```
