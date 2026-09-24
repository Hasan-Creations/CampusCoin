# Campus Coin — Distinctive Product Design & Tactile Physical Interaction Specification

## 1. Executive Identity & Aesthetic Thesis
Campus Coin is engineered with a **distinctive, authoritative visual identity** that deliberately stands out from standard SaaS templates and 20+ competing student teams without relying on decorative noise, cartoon animations, or visual clichés.

### The Identity Pillar: Warm Editorial Precision + Physical Instrument Depth
- **Tactile Physicality:** Controls feel mechanical and grounded. Buttons compress downward upon click with tangible resistance rather than floating or springing cartoonishly.
- **Warm Architectural Palette:** Anchored by Warm Ivory canvas, Deep Olive brand accents, and Muted Brass highlights, rejecting cold generic slate/blue palettes.
- **Intentional Geometry:** A strict hierarchy of corner radii matching structural purpose (16px cards, 22px dialogs, 10px inputs/buttons, 4px badges), deliberately avoiding ubiquitous 8px monotony.
- **Solid Material Depth:** Zero glassmorphism, zero `backdrop-blur`, and zero floating gradient blobs. Depth is achieved strictly through calibrated hairline borders, subtle tonal insets, and physical contact shadows.
- **Financial Authority:** Monospaced figures with tabular numbers (`tabular-nums`) and strict double-entry ledger alignment communicate fiscal clarity and credibility.

---

## 2. Locked Color Token System

### Light Mode Foundations
| Token | Hex Value | Purpose |
| :--- | :--- | :--- |
| `--bg-canvas` | `#F5EFE3` | Warm Ivory page foundation |
| `--bg-surface` | `#FCFAF6` | Rested cards, containers, sidebars |
| `--bg-surface-elevated` | `#FFFFFF` | Modal dialogs, dropdown popovers, active layers |
| `--bg-subtle` | `#EFE8DA` | Inset metric blocks, table headers, segmented bars |
| `--bg-subtle-hover` | `#E8DFCDB8` | Inset hover state |
| `--border-hairline` | `#D8C9A8` | Warm beige structural 1px bounding lines |
| `--border-subtle` | `#E5DBC5` | Inset tile borders |
| `--border-strong` | `#BAAA83` | Hovered and focused bounding lines |
| `--text-primary` | `#232B14` | Deep olive-charcoal body and heading typography |
| `--text-secondary` | `#4A5336` | Secondary body text and subtitles |
| `--text-muted` | `#687250` | Table headers, metadata, field descriptions |
| `--text-subtle` | `#8E9876` | Inactive dates and subtle icons |
| `--accent-primary` | `#4F5B2A` | Deep Olive brand anchor, primary action fills |
| `--accent-hover` | `#3E4720` | Primary action hover fill |
| `--accent-active` | `#2F3617` | Primary action pressed fill |
| `--accent-tint` | `#E8EFE1` | Deep Olive tinted backgrounds |
| `--gold` | `#B8892D` | Muted Brass highlights, allowance chips, badges |
| `--gold-hover` | `#9E731F` | Brass interactive hover |
| `--gold-tint` | `#F9F2E3` | Brass tinted badge fills |
| `--success` | `#3D6633` | Warm Olive Green positive cash-flow indicator |
| `--success-tint` | `#EBF3E8` | Income status pill fills |
| `--danger` | `#A83232` | Warm Brick Red outflow and expense indicator |
| `--danger-tint` | `#FBEAEA` | Expense status pill fills |

### Dark Mode (Night Mode) Foundations
The dark mode preserves the warm olive identity rather than defaulting to cold pitch black:
| Token | Hex Value | Purpose |
| :--- | :--- | :--- |
| `--bg-canvas` | `#14170F` | Deep Olive-Charcoal Night canvas |
| `--bg-surface` | `#1B2015` | Resting surface layer |
| `--bg-surface-elevated` | `#22281B` | Elevated modal dialogs and sheets |
| `--bg-subtle` | `#28301F` | Dark inset metric tiles and segmented bars |
| `--border-hairline` | `#343D2A` | Warm dark olive structural hairline lines |
| `--border-strong` | `#47543A` | Hovered border lines |
| `--text-primary` | `#F5EFE3` | Warm ivory contrast text |
| `--text-secondary` | `#D8CEBC` | Secondary ivory text |
| `--text-muted` | `#A6AF94` | Soft olive-khaki metadata |
| `--accent-primary` | `#8EA055` | Luminous Warm Olive interactive elements |
| `--accent-hover` | `#9FB264` | Luminous hover state |
| `--gold` | `#D6A449` | Luminous Antique Brass accents |
| `--success` | `#6CAE5C` | Warm luminous green positive indicators |
| `--danger` | `#DB5454` | Warm brick red expense indicators |

---

## 3. Strict Anti-Cliché Boundaries
1. **NO Glassmorphism & NO Backdrop Blur:** Every instance of `backdrop-blur-*` has been eliminated. Modals render with solid physical backdrops (`bg-black/55`) and solid elevated card surfaces (`bg-[var(--bg-surface-elevated)]`).
2. **NO Pill Buttons:** Interactive controls maintain an intentional `10px` curvature (`rounded-[10px]`); `rounded-full` is restricted solely to circular category color swatches, status dots, and avatars.
3. **NO Universal 8px Rounding:** Surfaces follow an architectural radius hierarchy:
   - **Surfaces & Cards:** `16px` (`rounded-[16px]` / `var(--radius-lg)`)
   - **Modals & Dialogs:** `22px` (`rounded-[22px]` / `var(--radius-xl)`)
   - **Buttons, Inputs & Tiles:** `10px` (`rounded-[10px]` / `var(--radius-md)`)
   - **Badges & Status Chips:** `4px` (`rounded-[4px]` / `var(--radius-xs)`)
4. **NO Generic SaaS Gradients or Neon Blobs:** All chromatic relationships are defined by warm earth-derived tokens.
5. **NO Unstyled Emojis:** Native SVG stroke icons with consistent `1.75px` stroke weights throughout.

---

## 4. Tactile Physical Interaction Mechanics
All interactive elements implement physical four-state mechanical feedback:
1. **Resting (Idle):**
   - Flat solid surface with subtle hairline contact shadow (`--shadow-tactile-sm`).
2. **Hover (Elevation):**
   - Micro-lift upward: `transform: translateY(-1px)`.
   - Contact shadow expansion: `--shadow-tactile-md`.
3. **Pressed Compression (Active):**
   - Physical mechanical compression: `transform: translateY(1.5px) scale(0.988)`.
   - Compression timing: ultra-fast `60ms` response (`transition-duration: 60ms`).
   - Shadow flattens into surface: `box-shadow: 0 1px 1px rgba(35, 43, 20, 0.10)`.
4. **Release (Settled):**
   - Returns to resting position via custom natural cubic-bezier easing (`cubic-bezier(0.16, 1, 0.3, 1)`).

### Core Reusable CSS Components (`resources/css/app.css`)
- `.btn-primary`: Olive anchor button with mechanical compression, white text, and inset top-highlight.
- `.btn-secondary`: Warm surface button with tactile hairline border and hover elevation.
- `.btn-icon`: 38×38px mechanical square icon action button.
- `.input-campus`: Warm surface input with 10px radius, hairline border, and olive focus ring.
- `.segmented-bar` & `.segmented-item`: Mechanical inset segmented toggle bar with elevated active indicator.
- `.card-campus`: 16px radius solid card with subtle contact shadow and hairline border.
- `.metric-tile`: Inset container for secondary statistics with hairline border.
- `.table-row-tactile`: Ledger table row with tactile hover background transition.
- `.modal-dialog-surface`: 22px radius elevated dialog container with solid physical depth (`--shadow-modal`).

---

## 5. Typography & Financial Data Authority
- **Headings:** `Space Grotesk`, weights 600, 700 with `-0.015em` tracking.
- **Body & UI Chrome:** `Inter`, weights 400, 500, 600.
- **Financial Figures, Dates & Codes:** `JetBrains Mono`, weights 500, 600, 700 with explicit `font-feature-settings: "tnum" 1` and `tabular-nums`. Every dollar amount aligns with exact column verticality.

---

## 6. Native SVG Chart Modernization
Interactive charts (Cash Flow trend lines, outflow bars, velocity indicators) in `resources/views/livewire/student/dashboard.blade.php` and `resources/views/livewire/student/monthly-reports.blade.php` were redesigned:
- **Inflow / Receipts:** `text-[var(--accent-primary)] fill-current` (warm deep olive in light, luminous olive in dark).
- **Outflow / Expenses:** `text-[var(--danger)] fill-current` (warm brick red).
- **Gridlines & Ticks:** `text-[var(--border-hairline)] stroke-current` with dashed hairline styling (`stroke-dasharray="3 3"`).
- **Tooltips & Value Labels:** Solid physical cards with `tabular-nums`.

---

## 7. Accessibility Preservation (Phase 8 Compliance)
The visual redesign retains 100% of Phase 8 accessibility and keyboard controls:
- **Skip Navigation Link:** `.skip-to-content` targeting `<main id="main-content" tabindex="-1">`.
- **High-Contrast Visible Focus Rings:** `:focus-visible` with `outline: 2px solid var(--accent-primary) !important; outline-offset: 2px !important`.
- **Root Typography Scaling:** Three-tier scaling via `html[data-font-size="normal|large|xlarge"]` (`100%`, `112.5%`, `125%`).
- **Vestibular Motion Protection:** Full `@media (prefers-reduced-motion: reduce)` block nullifying all CSS animations and transform translations.
- **Semantic Dialogs:** `role="dialog"`, `aria-modal="true"`, `aria-labelledby`, and Alpine `@keydown.escape.window` dismiss across all 8 application modals.
- **Accessible Table Headers:** `<th scope="col">` with `aria-sort` indicators across all ledger views.

---

## 8. Verification & Quality Metrics
- **Automated PHPUnit Tests:** 151 tests, 722 assertions, 100% passing.
- **Laravel Pint Code Formatter:** 0 style violations, clean formatting.
- **Vite Production Asset Build:** Clean compilation in ~4s (`app.css` 86kB / 14.7kB gzip, `app.js` 0.38kB).
