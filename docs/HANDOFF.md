# Campus Coin — Developer & AI Agent Handoff Guide

> "If I know nothing about this project and open this repository right now, what do I need to know before touching the code?"

## 1. Technology Stack
- **Framework:** Laravel 12 (PHP 8.4+)
- **Dynamic Frontend:** Laravel Livewire 3 + Blade
- **Styling:** Custom CSS design system + Tailwind CSS (configured strictly to Campus Coin tokens)
- **Database:** MySQL / MariaDB (Database: `campus_coin`)
- **Asset Bundler:** Vite

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
- **Default Student User:**
  - Email: `alex.rivera@campus.edu`
  - Password: `StudentSecure123!`
  - Role: `student`
  - Cohort: `Junior`
  - Monthly Allowance: `$1,200.00`
  - Target Savings Goal: `$300.00`

---

## 4. Crucial Business & Architectural Rules
1. **Never use floats for money:** All monetary numbers must be `DECIMAL(10,2)` in database and string/BCMath in business logic.
2. **Strict Data Isolation:** Never query transactions, budgets, or categories without scoping to `where('user_id', Auth::id())`. A student must never see another student's data.
3. **No Design Anti-Patterns:**
   - No pill buttons (no `rounded-full` or 9999px radius).
   - No glassmorphism, blur, or purple/pink gradients.
   - Use clean 1px hairline borders (`#E2E8F0` light, `#27272A` dark).
   - Fonts: Headings (`Space Grotesk`), UI/Body (`Inter`), Figures/Dates (`JetBrains Mono`).
4. **AI is strictly advisory:** Never force or automatically apply AI suggestions without student confirmation. Core expense tracking must work even if AI is disabled.

---

## 5. Current Implementation Status
- **Phase 0 (Foundation):** COMPLETED & VERIFIED.
  - Laravel 12 + Livewire 3 + MySQL operational.
  - User model, schema, and seeders active.
  - Campus Coin design tokens and hairline border components active.
  - Authentication flow (Student login, Register with cohort/.edu, direct Admin access, logout) active.
  - 11 automated feature tests passing with 45 assertions.
  - Homepage with SRS §5.3 sitemap active.
- **Immediate Next Action (Phase 1 — Core Student Data):**
  - Implement `Category` model, migration, default categories seeder, and user personal categories interface.
  - Implement `Transaction` model, migration (`DECIMAL(10,2)`), payment methods, recurring transaction flag, and CRUD modal/list.

---

## 6. Tests to Run
```powershell
php artisan test
```
