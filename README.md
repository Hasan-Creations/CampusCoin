# Campus Coin — Smart Spending, Student Style

Campus Coin is a full-stack student budgeting and expense-tracking web application designed to empower university students with deterministic financial control, realistic budget planning, cash-flow visibility, and advisory AI insights.

---

## Features
- **Student Profile & Cohorts:** Tailored to academic years (Freshman, Sophomore, Junior, Senior, Graduate) with customizable monthly allowances and savings targets.
- **Precision Ledger:** Accurate tracking of income and expenses without floating-point errors.
- **Interactive Budgeting:** Category caps with clear threshold alerts (Safe, Warning, Danger).
- **Advisory AI Categorization:** Machine-assisted expense tagging that stays strictly advisory.
- **Executive Admin Controls:** Global category management and student account administration.
- **Fintech Precision Design:** Strict adherence to data-dense, flat, hairline-border aesthetics inspired by Linear and Stripe.

---

## Technology Stack
- **Framework:** Laravel 12 (PHP 8.4+)
- **Presentation:** Blade Templates + Livewire 3
- **Database:** MySQL / MariaDB (InnoDB, UTF-8 MB4)
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

# Configure database in .env
# DB_DATABASE=campus_coin
# DB_USERNAME=root
# DB_PASSWORD=

# Run migrations and seed database
php artisan migrate:fresh --seed

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
Comprehensive, persistent project documentation is maintained in `/docs/`:
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
php artisan test
```
