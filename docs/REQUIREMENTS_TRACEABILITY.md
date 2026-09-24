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
| **Student Profile Baselines** | SRS §4.1 | Phase 0 | **VERIFIED** | `User` attributes: academic year, allowance, savings goal | `CampusCoinAuthTest.php` passed |
| **Personal Categories CRUD** | SRS §4.2 | Phase 1 | **VERIFIED** | `Category` model, Livewire `CategoryManager` | `CategoryManagementTest.php` (8 tests passed) |
| **Default System Categories** | SRS §4.2, §4.11 | Phase 1 | **VERIFIED** | 12 global categories seeded, read-only display | `CategoryManagementTest.php` passed |
| **Income & Expense Entry** | SRS §4.3 | Phase 1 | **VERIFIED** | `Transaction` model, Livewire `TransactionList` modal | `TransactionLedgerTest.php` passed |
| **Transaction History & Filter** | SRS §4.3 | Phase 1 | **VERIFIED** | Livewire `TransactionList` with search & filters | `TransactionLedgerTest.php` passed |
| **Recurring Transactions** | SRS §4.3 | Phase 1 | **VERIFIED** | `Transaction` recurrence flag & filters | `TransactionLedgerTest.php` passed |
| **Dashboard KPIs & Ledger** | SRS §4.4 | Phase 1 | **VERIFIED** | Livewire `Dashboard` with income, expense, safe-to-spend | `CategoryManagementTest.php`, `DashboardTest` |
| **Category Budgets CRUD** | SRS §4.5 | Phase 2 | **VERIFIED** | `Budget` model, Livewire `BudgetManager` (`/budgets`) | `BudgetGoalsTest.php` (22 tests passed) |
| **Budget Consumption Calculation** | SRS §4.5 | Phase 2 | **VERIFIED** | Dynamic aggregation from ledger expense transactions | `BudgetGoalsTest.php` & Playwright E2E passed |
| **Budget Threshold Alerts** | SRS §4.5 | Phase 2 | **VERIFIED** | <75% Green, 75–100% Amber, >100% Danger & alerts | `BudgetGoalsTest.php` & Playwright E2E passed |
| **Spending Breakdown & Trends** | SRS §4.4 | Phase 3 | **VERIFIED** | `FinancialCalculationService`, `Dashboard.php`, SVG Cash Flow Chart | `CashFlowTrendsTest.php` (10 tests, 103 assertions passed) |
| **Monthly Financial Reports** | SRS §4.6 | Phase 4 | **VERIFIED** | `MonthlyReports.php`, `FinancialCalculationService` | `MonthlyReportsTest.php` (14 tests passed) |
| **Export Reports (CSV/PDF)** | SRS §4.6 | Phase 4 | **VERIFIED** | `ReportExportController.php`, `pdf.blade.php`, Dompdf | `MonthlyReportsTest.php` (14 tests passed) |
| **Deterministic Saving Tips** | SRS §4.7 | Phase 5 | **VERIFIED** | `SavingTipsService`, `SavingTip` model, `SavingTipsManager` (`/tips`), `Dashboard.php` widget | `SavingTipsTest.php` (19 tests, 82 assertions passed) |
| **Pin / Bookmark Tips & Insights** | SRS §4.7, §4.9 | Phase 5 | **VERIFIED** | `saving_tips` status column (`pinned`, `dismissed`), `SavingTip` methods, `SavingTipsManager` tabs & actions, Dashboard quick-actions | `SavingTipsTest.php` passed |
| **Advisory AI Categorization** | SRS §4.8 | Phase 6 | **VERIFIED** | `AiCategorizationService`, `CategorizationProviderInterface`, `HeuristicCategorizationProvider`, `OpenAiCategorizationProvider` | `AiCategorizationTest.php` (15 tests, 70 assertions passed) |
| **User Override of AI Suggestions** | SRS §4.8 | Phase 6 | **VERIFIED** | `TransactionList.php` (`acceptSuggestion`, `selectCategory`, manual priority) | `AiCategorizationTest.php` (manual override tests passed) |
| **Student-Specific Learned Corrections** | SRS §4.8 | Phase 6 | **VERIFIED** | `CategoryLearning` model, `category_learnings` schema, `recordCorrection()` | `AiCategorizationTest.php` (isolation & learning tests passed) |
| **Historical CSV Import with Batch Suggestions** | SRS §4.3, §4.8 | Phase 6 | **VERIFIED** | `TransactionList.php` (`processCsvUpload`, `confirmImport`, review modal) | `AiCategorizationTest.php` (batch & invalid row tests passed) |
| **Operational Admin Dashboard** | SRS §4.11 | Phase 7 | **VERIFIED** | `AdminDashboard.php`, `CategoryManager.php`, `UserManager.php`, `AdminMetricsService.php`, session termination on deactivation, category safe deletion | `AdminManagementTest.php` (28 tests, 107 assertions passed) |
| **Dark Mode & Accessibility** | SRS §1.6, §5.1, §185, UI Spec | Phase 8 | **VERIFIED** | CSS root tokens, synchronous head boot script, font scaling dropdown, high-contrast `:focus-visible`, skip-to-content links, ARIA dialog semantics, `<th scope="col">` headers, input `autocomplete`, `role="status"`/`role="alert"` announcements | `AccessibilityAndHardeningTest.php` (12 tests, 74 assertions passed) |
| **Full Data Isolation Guard** | Security Spec | Continuous | **VERIFIED** | Scoped queries, 403 authorization, cross-student test | Automated tests & Playwright E2E verified |
