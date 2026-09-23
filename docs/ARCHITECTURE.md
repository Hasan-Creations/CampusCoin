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
│   │   └── MonthlyReports.php
│   └── Admin/
├── Models/
│   ├── User.php
│   ├── Category.php
│   ├── Transaction.php
│   ├── Budget.php
│   ├── SavingTip.php
│   └── MonthlyInsight.php
├── Services/
│   ├── FinancialCalculationService.php
│   ├── AiAdvisorService.php
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
- **Tenant Isolation:** Every financial query is scoped to `where('user_id', Auth::id())`. No student may inspect or modify another student's categories, budgets, or transactions.

---

## 6. AI Integration Boundary
- AI functionalities (transaction categorization and monthly advisory tips) reside behind an isolated service interface (`AiAdvisorService`).
- If an external AI provider fails or is unreachable, fallback deterministic rules apply seamlessly without interrupting student workflow.
- All AI recommendations are advisory and require explicit user consent/override.
