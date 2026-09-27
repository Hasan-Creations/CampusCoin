# Campus Coin — Smart Spending, Student Style

Campus Coin is a student budgeting and expense-tracking web application for recording income, planning budgets, reviewing cash flow, and getting optional AI suggestions.

---

## Features
- **Student Profile & Cohorts:** Supports academic years from Freshman through Graduate, with monthly allowances and savings targets.
- **Editable Student Profile:** Update name, academic year, allowance baseline, and savings goal.
- **Password Recovery:** Email reset links use Laravel's expiring, token-based password broker.
- **Precision Ledger:** Accurate income and expense tracking, monthly recurring entries, and retained change history.
- **Dashboard Quick Add:** Record income or expenses without leaving the dashboard.
- **Interactive Budgeting:** Category caps with clear threshold alerts (Safe, Warning, Danger).
- **Deterministic Saving Tips:** Finds saving opportunities from spending history, budget limits, and allowance use.
- **Advisory AI Categorization:** Suggests expense categories with a local fallback and remembers student corrections.
- **CSV Batch Import & Categorization:** Bounded batch processing (up to 50 rows) with inline AI suggestions and review/override modal.
- **Executive Admin Controls:** Global categories, account activation and password reset, system tip/announcement templates, and usage statistics.
- **Fintech Precision Design:** Strict adherence to data-dense, flat, hairline-border aesthetics inspired by Linear and Stripe.

---

## Technology Stack
- **Framework:** Laravel 13 (PHP 8.3+)
- **Presentation:** Blade Templates + Livewire 4
- **Database:** SQLite by default; other Laravel relational drivers can be configured
- **Asset Bundler:** Vite
- **Styling:** Custom Design Tokens + Tailwind CSS

---

## Getting Started

### Prerequisites
- PHP >= 8.2 (Tested on PHP 8.4)
- Composer >= 2.0
- Node.js >= 20.x & NPM
- MySQL / MariaDB

### Installation
```powershell
# Clone or navigate to the repository
cd d:\CodingWizard\Projects\CampusCoin

# Install backend dependencies
composer install

# Install frontend dependencies
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Create the schema and seed evaluator accounts and sample data
php artisan migrate --seed

# Build assets
npm run build

# Start local server
php artisan serve
```

---

## Default User Credentials
| Role | Email | Password | Details |
| :--- | :--- | :--- | :--- |
| **System Administrator** | `admin@campuscoin.edu` | `AdminSecure123!` | Full admin access |
| **Student** | `alex.rivera@campus.edu` | `StudentSecure123!` | Junior cohort, $1,200/mo allowance |

---

## Architecture & Documentation
Project documentation is stored in `/docs/`:
- [`PROJECT_STATE.md`](./docs/PROJECT_STATE.md) — Current sprint, progress, and next steps.
- [`ARCHITECTURE.md`](./docs/ARCHITECTURE.md) — System layers, request lifecycle, and data flow.
- [`REQUIREMENTS_TRACEABILITY.md`](./docs/REQUIREMENTS_TRACEABILITY.md) — Traceability matrix mapped to the SRS.
- [`DATABASE.md`](./docs/DATABASE.md) — Relational schema, indices, and constraints.
- [`UI_IMPLEMENTATION.md`](./docs/UI_IMPLEMENTATION.md) — Visual tokens, typography, and anti-cliché design rules.
- [`DECISIONS.md`](./docs/DECISIONS.md) — Architectural decision records (ADRs).
- [`DEVELOPMENT_LOG.md`](./docs/DEVELOPMENT_LOG.md) — Chronological work log.
- [`HANDOFF.md`](./docs/HANDOFF.md) — Developer briefing for project continuation.

---

## Running Automated Tests
```powershell
php artisan test --configuration=miscellaneous/phpunit.xml
```
