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
| **Framework & Database Bootstrap** | Project Charter | Phase 0 | **VERIFIED** | Laravel 13.17 + Livewire 4.4; SQLite default with Laravel relational-driver configuration | `php artisan test --configuration=miscellaneous/phpunit.xml` |
| **Password Recovery and Reset** | SRS §1.6 | Phase 0 | **VERIFIED** | Laravel password broker, email notification, 60-minute tokens, hashed password update | `CampusCoinAuthTest.php`: request, valid/expired/invalid token, changed-password login |
| **Student Registration** | SRS §4.1 | Phase 0 | **VERIFIED** | `RegisterController`, `User` schema, `.edu` check | `test_new_students_can_register...` passed |
| **Student Login** | SRS §4.1 | Phase 0 | **VERIFIED** | `LoginController`, 60/40 Auth view | `test_student_can_authenticate...` passed |
| **Direct Admin Access** | SRS §4.1, §4.11 | Phase 0 | **VERIFIED** | `role=admin`, `EnsureUserIsAdmin`, `/admin/login` | `test_administrators_can_authenticate...` passed |
| **Homepage Sitemap** | SRS §5.3 | Phase 0 | **VERIFIED** | `welcome.blade.php` with sitemap navigation | `test_welcome_page_renders_with_sitemap` passed |
| **Design System & Tokens** | UI Spec | Phase 0 | **VERIFIED** | Space Grotesk, Inter, JetBrains Mono, CSS tokens | `npm run build` passed (0 warnings) |
| **Student Profile Creation and Editing** | SRS §1.6 | Phase 0 | **VERIFIED** | Registration plus student-only `/profile` edit for name, cohort, allowance, and savings goal | `CampusCoinAuthTest.php`: validation, owner scoping, student-only access |
| **Personal Categories CRUD** | SRS §4.2 | Phase 1 | **VERIFIED** | `Category` model, Livewire `CategoryManager` | `CategoryManagementTest.php` (8 tests passed) |
| **Default System Categories** | SRS §4.2, §4.11 | Phase 1 | **VERIFIED** | 12 global categories seeded, read-only display | `CategoryManagementTest.php` passed |
| **Income & Expense Entry** | SRS §4.3 | Phase 1 | **VERIFIED** | `Transaction` model, Livewire `TransactionList` modal | `TransactionLedgerTest.php` passed |
| **Transaction Search and Filters** | SRS §1.6 | Phase 1 | **VERIFIED** | Livewire `TransactionList` with search and filters | `TransactionLedgerTest.php` |
| **Retained Transaction Change History** | SRS §1.6 | Phase 1 | **VERIFIED** | `transaction_histories` stores user-scoped snapshots before edits and deletions; excluded from ledger totals | `TransactionLedgerTest.php`: edit, delete, retained values, tenant isolation |
| **Recurring Transaction Execution** | SRS §1.6 | Phase 1 | **VERIFIED** | Monthly `next_occurrence_date`, scheduled `transactions:generate-recurring`, unique source/date key | `TransactionLedgerTest.php`: due date, date rollover, duplicate prevention, ownership |
| **Dashboard Quick Add** | SRS §1.6 | Phase 1 | **VERIFIED** | Dashboard embeds the existing transaction component in quick-add-only mode | `TransactionLedgerTest.php`: income, expense, scoped categories, shared validation |
| **Dashboard KPIs & Ledger** | SRS §1.6 | Phase 1 | **VERIFIED** | Livewire `Dashboard` with income, expense, safe-to-spend, active campus updates | Dashboard and transaction feature tests |
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
| **Operational Admin Dashboard and Usage Statistics** | SRS §1.6 | Phase 7 | **VERIFIED** | `AdminMetricsService`, active-user counts, transaction totals, most-used categories, cohort counts | `AdminManagementTest.php` |
| **Admin User Controls and Account Reset** | SRS §1.6 | Phase 7 | **VERIFIED** | View/disable/enable student accounts; send Laravel password-reset links; financial baseline reset remains separate | `AdminManagementTest.php`: role guard, reset email, baseline behavior |
| **System Categories and Campus Tip Templates** | SRS §1.6 | Phase 7 | **VERIFIED** | Admin category controls plus CRUD for `system_tip_templates`; active messages display on student dashboard | `AdminManagementTest.php`: CRUD and student visibility |
| **Dark Mode, Font Size, and Accessibility** | SRS §1.6 | Phase 8 | **VERIFIED** | Persistent small/normal/large root font scaling, dark-mode boot, focus, skip link, dialog and table semantics | `AccessibilityAndHardeningTest.php` |
| **PDF Report Filter Privacy** | SRS §1.7 | Continuous | **VERIFIED** | Category filter labels resolve through `Category::forUser()` | `MonthlyReportsTest.php`: private category name not exposed |
| **Full Data Isolation Guard** | Security Spec | Continuous | **VERIFIED** | Scoped queries, 403 authorization, cross-student test | Automated tests & Playwright E2E verified |

## Final SRS Audit — 2026-09-27

| Requirement group | Status | Notes |
|---|---|---|
| Authentication, email reset, editable profile | **Implemented and verified** | Reset tokens expire after 60 minutes; reset email delivery is tested with Laravel notifications. |
| Categories, transaction entry, budgets, reports, tips, CSV, advisory categorization | **Implemented and verified** | Existing behavior and tests retained. |
| Monthly recurring entries | **Implemented and verified** | Production execution requires the deployment's Laravel scheduler to run. |
| Transaction edit/delete history | **Implemented and verified** | Prior snapshots remain available in the user's Transaction Change History; normal ledger totals use only active rows. |
| Dashboard quick-add | **Implemented and verified** | Reuses `TransactionList` validation and persistence. |
| Administrator controls and metrics | **Implemented and verified** | Includes user status, password-reset link, system categories, campus tip templates, active users, transaction totals, and most-used categories. |
| Accessibility font-size adjustment | **Implemented and verified** | Browser-local preference offers small, normal, and large sizes; dark mode remains independent. |
| Mandatory project report, evaluator README, SQL schema, submission ZIP, and home-page sitemap | **Present** | `CampusCoin_Project_Documentation.md`, `miscellaneous/ReadMe.doc`, `miscellaneous/CampusCoin_schema.sql`, `miscellaneous/CampusCoin_submission.zip`, and `/` respectively. |
| AI monthly narrative/history, forecasts, recently viewed entries, duplicate/anomaly detection, chatbot | **Optional** | Not implemented as these items are optional in the SRS. Deterministic saving tips remain distinct from AI-generated narratives. |
| Demonstration video | **Manual deliverable** | The mandatory `.mp4` walkthrough was not recorded in this environment. |
| Public hosting and production availability | **Environment/deployment verification** | Requires an actual deployment and scheduler configuration. |
