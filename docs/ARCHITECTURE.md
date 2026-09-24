# Campus Coin — Architecture

## 1. High-Level Overview
Campus Coin is an authoritative, full-stack student budgeting and expense tracking platform built strictly with:
- **Backend Framework:** Laravel 12 (PHP 8.4+)
- **Templating & Presentation:** Laravel Blade + Livewire 3
- **Relational Database:** MySQL/MariaDB (InnoDB, UTF-8 MB4)
- **Asset Bundler:** Vite
- **Styling Architecture:** Structured CSS adhering to fintech design tokens (Linear/Stripe aesthetic)

The application avoids unnecessary SPA/API complexity. All views are server-rendered with Blade, with Livewire providing high-performance reactive interfaces for dynamic filtering, modal interactions, and quick-add actions.

---

## 2. Request & Component Flow
1. **HTTP Ingestion:** Standard Laravel HTTP Kernel routes requests through `routes/web.php`.
2. **Middleware Pipeline:**
   - Standard: `EncryptCookies`, `AddQueuedCookiesToResponse`, `StartSession`, `ShareErrorsFromSession`, `ValidateCsrfToken`.
   - Security: `auth` (verifies active authenticated session), `EnsureUserIsActive` (ensures account is not disabled), `EnsureUserIsAdmin` (protects `/admin` routes).
3. **Controllers & Livewire Components:**
   - Standard HTTP Controllers handle authentication, file exports (CSV, PDF), and bulk imports.
   - Livewire components handle reactive single-page widgets (transaction table filtering, budget sliders, quick-add transaction modal).
4. **Data Access & Domain Logic:**
   - Strict user ownership scope: All student domain models (`Transaction`, `Budget`, `Category`, `Insight`, `TipBookmark`) enforce ownership through `user_id` linked directly to `Auth::id()`.
   - Client-provided `user_id` inputs are strictly ignored to prevent authorization bypass.

---

## 3. Directory Structure
```text
app/
├── Contracts/
│   └── CategorizationProviderInterface.php
├── DTO/
│   └── CategorySuggestion.php
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   │   ├── LoginController.php
│   │   │   ├── RegisterController.php
│   │   │   └── PasswordResetController.php
│   │   ├── Admin/
│   │   ├── ExportController.php
│   │   └── ReportExportController.php
│   └── Middleware/
│       ├── EnsureUserIsAdmin.php
│       └── EnsureUserIsActive.php
├── Livewire/
│   ├── Student/
│   │   ├── Dashboard.php
│   │   ├── TransactionList.php
│   │   ├── TransactionModal.php
│   │   ├── BudgetManager.php
│   │   ├── CategoryManager.php
│   │   ├── MonthlyReports.php
│   │   └── SavingTipsManager.php
│   └── Admin/
│       ├── Dashboard.php
│       ├── CategoryManager.php
│       └── UserManager.php
├── Models/
│   ├── User.php
│   ├── Category.php
│   ├── Transaction.php
│   ├── Budget.php
│   ├── CategoryLearning.php
│   ├── SavingTip.php
│   └── MonthlyInsight.php
├── Services/
│   ├── AdminMetricsService.php
│   ├── FinancialCalculationService.php
│   ├── SavingTipsService.php
│   ├── AiCategorizationService.php
│   ├── Categorization/
│   │   ├── HeuristicCategorizationProvider.php
│   │   └── OpenAiCategorizationProvider.php
│   └── CsvImportService.php
```

---

## 4. Financial Calculations & Monetary Representation
- Floating-point representations (`float`, `double`) are strictly prohibited for monetary balance and arithmetic.
- Relational fields use `DECIMAL(10, 2)`.
- Backend computations utilize deterministic precision routines (`bcmath` or string math) rounded half-up to 2 decimal places.

---

## 5. Security & Isolation Strategy
- **Authentication:** Bcrypt password hashing (minimum 12 rounds), session invalidation on logout.
- **Role Isolation:** Two roles (`student`, `admin`). Students can never access `/admin/*` routes.
- **Tenant Isolation:** Every financial, category, and personal data query is scoped to `where('user_id', Auth::id())`. Student corrections in `category_learnings` and saving tips in `saving_tips` are strictly tenant-isolated (`where('user_id', $userId)`) so one student's learning or tips never leak to another.

---

## 6. Deterministic Saving Tips Engine Architecture
- **Rule Evaluator (`SavingTipsService`):**
  - Evaluates 5 explicit financial rules over actual student ledger data:
    1. Historical spending spike (>20% over 3-month average, min $15 delta).
    2. Category budget threshold alerts (>=80% warning, >100% exceeded).
    3. Category concentration (>40% of total monthly expenses).
    4. Month-over-month overall expense growth (>25% surge, min $50 delta).
    5. Savings goal lagging (net balance under student's monthly savings target).
  - Calculates potential savings impact using BCMath arithmetic.
  - Deterministically ranks opportunities by calculated savings descending.
- **Stateful Persistence Model:**
  - `saving_tips` table with composite unique index `(user_id, rule_key, category_id)`.
  - Ensures tip sync operations are idempotent: active tips update their live metrics, newly triggered tips are inserted, resolved/obsolete tips are purged, and student manual actions (`pinned`, `dismissed`) are strictly preserved.

---

## 7. AI Categorization & Advisory Integration Boundary
- **Advisory Architecture:** The expense categorization assistant is an advisory utility behind `CategorizationProviderInterface`. It never silently overrides student category selections.
- **Student Learned Feedback Layer:** `AiCategorizationService` checks student-specific learned corrections from `CategoryLearning` first. User choices take precedence over external AI.
- **Deterministic Fallback:** If `OpenAiCategorizationProvider` fails, times out (3-second strict network limit), is unconfigured, or returns an invalid/hallucinated category, it seamlessly falls back to `HeuristicCategorizationProvider`.
- **Validation Shield:** All suggestions are checked against the student's available categories (`Category::forUser($userId)`). External models can never invent or assign non-existent categories.
- **Zero Secret Leakage:** AI API keys are stored server-side via `config/services.php` and `.env`; no secrets are ever exposed to the client browser.

---

## 8. Operational Admin Panel & Category Governance Architecture
- **Multi-Layered Admin Guard:**
  - Route Layer: `EnsureUserIsAdmin` restricts all `/admin/*` routes to accounts where `$user->isAdmin()` is true.
  - Component Lifecycle Layer: `mount()` and `boot()` guards in all admin Livewire components guarantee that mutations cannot be invoked even if route middleware was bypassed.
- **Centralized Account Status Enforcement:**
  - `EnsureUserIsActive` is registered in the global `web` middleware pipeline. Any authenticated student whose account is marked `disabled` is immediately logged out, their session is invalidated, and requests are aborted/redirected to login.
  - Deactivating an account via `User::deactivate()` directly purges corresponding session records from the `sessions` database table, terminating any concurrent active browser sessions.
- **Non-Destructive Category Archival & Safe Deletion:**
  - Categories feature an `is_active` boolean column. Deactivated categories are immediately hidden from student creation/budget dropdowns, but remain intact in the database so historical transactions, reports, and ledger entries remain fully interpretable.
  - Hard deletion is protected by `canBeSafelyDeleted()`, which enforces that categories with referencing transactions, budgets, saving tips, or learned mappings cannot be hard deleted.
- **High-Performance Telemetry Engine:**
  - `AdminMetricsService` utilizes single-pass SQL aggregate functions (`COUNT`, `SUM`, `AVG`, `GROUP BY`) to compute platform volume, student active rates, category adoption, and the SRS-mandated Most-Used Categories leaderboard without loading raw Eloquent collections into PHP memory. Zero division and empty database states are strictly guarded.
