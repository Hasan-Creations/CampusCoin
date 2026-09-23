# Campus Coin — Requirements Traceability Matrix

## Status Definitions
- `NOT_STARTED`: Work has not begun.
- `IN_PROGRESS`: Currently being implemented.
- `IMPLEMENTED`: Code exists but automated/manual verification is pending.
- `VERIFIED`: Functionality confirmed through executed automated tests or explicit manual checks.
- `BLOCKED`: Implementation halted by an external dependency or critical issue.
- `DEFERRED`: Scheduled for a future release cycle.

---

## Traceability Matrix

| Requirement | Source | Target Phase | Status | Implementation Reference | Verification Reference |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Framework & Database Bootstrap** | Project Charter | Phase 0 | **VERIFIED** | Laravel 12 + Livewire 3 + MySQL `campus_coin` | `php artisan migrate:fresh` passed |
| **Student Registration** | SRS §4.1 | Phase 0 | **VERIFIED** | `RegisterController`, `User` schema, `.edu` check | `test_new_students_can_register...` passed |
| **Student Login** | SRS §4.1 | Phase 0 | **VERIFIED** | `LoginController`, 60/40 Auth view | `test_student_can_authenticate...` passed |
| **Direct Admin Access** | SRS §4.1, §4.11 | Phase 0 | **VERIFIED** | `role=admin`, `EnsureUserIsAdmin`, `/admin/login` | `test_administrators_can_authenticate...` passed |
| **Homepage Sitemap** | SRS §5.3 | Phase 0 | **VERIFIED** | `welcome.blade.php` with sitemap navigation | `test_welcome_page_renders_with_sitemap` passed |
| **Design System & Tokens** | UI Spec | Phase 0 | **VERIFIED** | Space Grotesk, Inter, JetBrains Mono, CSS tokens | `npm run build` passed (0 warnings) |
| **Student Profile Setup** | SRS §4.1 | Phase 1 | `NOT_STARTED` | `User` attributes: academic year, allowance, savings goal | Phase 0 baseline stored |
| **Personal Categories CRUD** | SRS §4.2 | Phase 1 | `NOT_STARTED` | `Category` model, Livewire `CategoryManager` | — |
| **Default System Categories** | SRS §4.2, §4.11 | Phase 1 | `NOT_STARTED` | Global categories seeded, admin managed | — |
| **Income & Expense Entry** | SRS §4.3 | Phase 1 | `NOT_STARTED` | `Transaction` model, Livewire `TransactionModal` | — |
| **Transaction History & Filter** | SRS §4.3 | Phase 1 | `NOT_STARTED` | Livewire `TransactionList` | — |
| **Recurring Transactions** | SRS §4.3 | Phase 1 | `NOT_STARTED` | `Transaction` recurrence rules | — |
| **Dashboard KPIs & Cash Flow** | SRS §4.4 | Phase 2 | `NOT_STARTED` | Livewire `Dashboard` with 12-col grid | — |
| **Spending Breakdown & Trends** | SRS §4.4 | Phase 2 | `NOT_STARTED` | SVG/Canvas 6-month trends | — |
| **Category Budgets** | SRS §4.5 | Phase 3 | `NOT_STARTED` | `Budget` model, Livewire `BudgetManager` | — |
| **Budget Threshold Alerts** | SRS §4.5 | Phase 3 | `NOT_STARTED` | <75% Green, 75-99% Amber, >=100% Danger | — |
| **Monthly Financial Reports** | SRS §4.6 | Phase 4 | `NOT_STARTED` | `ReportController`, 6-month comparisons | — |
| **Export Reports (CSV/PDF)** | SRS §4.6 | Phase 4 | `NOT_STARTED` | `ExportController` | — |
| **Deterministic Saving Tips** | SRS §4.7 | Phase 5 | `NOT_STARTED` | `SavingTipService` | — |
| **Pin / Bookmark Tips & Insights** | SRS §4.7, §4.9 | Phase 5 | `NOT_STARTED` | `Bookmark` pivot table & actions | — |
| **Advisory AI Categorization** | SRS §4.8 | Phase 6 | `NOT_STARTED` | `AiAdvisorService` | — |
| **User Override of AI Suggestions** | SRS §4.8 | Phase 6 | `NOT_STARTED` | Interactive review modal | — |
| **Historical CSV Import with Safety** | SRS §4.3, §4.8 | Phase 6 | `NOT_STARTED` | `CsvImportService` with duplicate detection | — |
| **Advisory AI Monthly Insights** | SRS §4.9 | Phase 6 | `NOT_STARTED` | `AiInsightService` | — |
| **Operational Admin Dashboard** | SRS §4.11 | Phase 7 | `NOT_STARTED` | Admin metrics, user toggle, system categories | Base layout verified |
| **Dark Mode & Accessibility** | SRS §5.1, UI Spec | Phase 8 | `NOT_STARTED` | CSS tokens, contrast, theme toggler | Base token system verified |
| **Full Data Isolation Guard** | Security Spec | Continuous | **VERIFIED** | Scoped queries, `test_students_are_blocked...` | 403 authorization tested |
