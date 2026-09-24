# Campus Coin — UI Implementation Specification

## 1. Design Direction
**Precision-oriented fintech dashboard + student product**
- References: Linear, Stripe Dashboard, Copilot Money
- Aesthetic: Data-dense, flat, structured 12-column grid, hairline borders (1px), high typographic contrast, no visual fluff.

---

## 2. Anti-Cliché Rules (Strictly Enforced)
- **NO** pill-shaped buttons (`rounded-full` or 9999px radius).
- **NO** huge rounded cards.
- **NO** purple/blue gradients or pink/orange gradients.
- **NO** glassmorphism, frosted glass, or `backdrop-blur`.
- **NO** floating background blobs or particle effects.
- **NO** 3D illustrations or isometric graphics.
- **NO** emoji icons in UI chrome (SVG Lucide line icons only).
- **NO** hiding important financial figures inside accordions.

---

## 3. Typography System
Fonts loaded via Google Fonts CDN:
- **Headings:** `Space Grotesk`, weights 600, 700
- **Body & Controls:** `Inter`, weights 400, 500, 600
- **Financial Figures & Dates:** `JetBrains Mono`, weights 500, 600 (with `font-feature-settings: "tnum"`)

### Type Scale
- `H1`: 24px, 700, -0.02em tracking
- `H2`: 18px, 600, -0.01em tracking
- `H3`: 14px, 600
- `Body`: 14px, 400/500, line-height 1.5
- `Subtitle`: 13px, 400, muted
- `Labels`: 11px, 600, uppercase, letter-spacing 0.05em

---

## 4. Color Tokens

### Light Mode (`:root` / default)
- `--bg-canvas`: `#F8FAFC`
- `--bg-surface`: `#FFFFFF`
- `--bg-subtle`: `#F1F5F9`
- `--border-hairline`: `#E2E8F0`
- `--text-primary`: `#0F172A`
- `--text-muted`: `#64748B`
- `--accent-primary`: `#059669`
- `--accent-hover`: `#047857`
- `--accent-tint`: `#ECFDF5`
- `--gold`: `#D97706`
- `--danger`: `#E11D48`

### Dark Mode (`.dark`)
- `--bg-canvas`: `#09090B`
- `--bg-surface`: `#18181B`
- `--bg-subtle`: `#27272A`
- `--border-hairline`: `#27272A`
- `--text-primary`: `#FAFAFA`
- `--text-muted`: `#A1A1AA`
- `--accent-primary`: `#10B981`
- `--accent-hover`: `#34D399`
- `--accent-tint`: `#064E3B`
- `--gold`: `#F59E0B`
- `--danger`: `#F43F5E`

---

## 5. Spacing & Radius System
- **Radius Small (Badges, Tags):** `4px`
- **Radius Medium (Inputs, Buttons):** `6px`
- **Radius Large (Cards, Modals):** `8px`
- **Spacing Scale:** `4px`, `8px`, `12px`, `16px`, `24px`, `32px`, `48px`
- **Card Specification:** 1px solid border, 8px radius, 20px padding, no heavy elevation.

---

## 6. Iconography
Lucide-compatible SVG stroke icons (`stroke-width="1.75"`):
- `Wallet`, `Lock`, `ArrowRight`, `CheckCircle2`, `GraduationCap`, `DollarSign`, `Target`, `ShieldCheck`, `TrendingUp`, `TrendingDown`, `PieChart`, `Plus`, `Lightbulb`, `CreditCard`, `Search`, `Filter`, `Download`, `Calendar`, `Tag`, `Users`, `Activity`, `Database`, `ShieldAlert`, `Sliders`.

---

## 7. Responsive Breakpoints
- `xl`: `>= 1200px` (Multi-column dashboard, 12-col grid)
- `md`: `>= 768px` (Tablet 2×2 grid, collapsible sidebar)
- `sm`: `< 768px` (Single column, horizontal scrolling KPI bar, bottom action sheet for transaction modal)

---

## 8. Admin Operations & Governance Interface *(Phase 7)*
- **Layout Architecture (`resources/views/layouts/admin.blade.php`):**
  - Dedicated admin shell with left-hand operational sidebar, root operator profile badge, and dark/light mode toggle.
  - Active route highlighting across Overview (`admin.dashboard`), Student Accounts (`admin.users`), and Global Categories (`admin.categories`).
- **Operational Dashboard (`livewire/admin/dashboard.blade.php`):**
  - 4 primary telemetry KPI cards: Total Students, Tracked Ledger Volume, Logged Transactions, and Category Governance.
  - 12-column responsive layout: 7-column Most-Used Categories leaderboard with volume shares; 5-column Student Demographics & Commitments progress card.
  - Recent registered campus accounts table with cohort details, ledger activity counts, and inline status toggle action.
- **Global Category Manager (`livewire/admin/category-manager.blade.php`):**
  - Segmented scope tabs (Global Defaults vs Student Custom vs All Categories).
  - Type filters (`income`, `expense`), status filters (`active`, `inactive`), and real-time debounced search.
  - Modal form for creating and editing global default categories with icon palette and hex accent selectors.
  - Safe non-destructive deletion and status toggle with visual badges.
- **Student Account Manager (`livewire/admin/user-manager.blade.php`):**
  - Status, cohort, and role filters with sorting by newest, name, or transaction activity.
  - Interactive inspection modal displaying full profile, baseline allowances, and aggregated ledger telemetry without exposing sensitive credentials.
  - One-click account deactivation/reactivation and baseline reset actions with confirmation dialogues.
