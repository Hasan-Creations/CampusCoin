# Campus Coin — UI Screenshot Coverage Matrix

This matrix provides a structured, exhaustive cross-reference of all application views, responsive viewports, thematic modes, accessibility font-scaling factors, and operational UI states captured during the visual audit.

All screenshots correspond to the live rendered state of Campus Coin and reside under [`docs/ui-screenshots/`](file:///d:/CodingWizard/Projects/CampusCoin/docs/ui-screenshots).

---

## 1. Visual Coverage Matrix

| Area / Feature | Route | Default (1440×900) | Mobile (390×844) | Tablet (1024×768) | Dark Mode | Font Scaling | Modal / Dialog | Dropdown / Menu | Error / Validation | Empty State | Loading / Telemetry |
| :--- | :--- | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| **Welcome / Landing** | `/` | [01, 02] | [03] | [04] | — | — | — | — | — | — | — |
| **Student Login** | `/login` | [05] | [08] | — | — | — | — | — | [07] | — | — |
| **Student Register** | `/register` | [09] | [14] | — | — | — | — | — | [13] | — | [11, 12] (Edu) |
| **Admin Login** | `/admin/login` | [15] | [17] | — | — | — | — | — | [16] | — | — |
| **Student Shell & Nav** | `/*` | [18] | [23, 24] | [25] | [20] | [21, 22] | — | [19] | — | — | — |
| **Student Dashboard** | `/dashboard` | [26, 27] | [38] | — | [35] | [36, 37] | — | [30, 31] | — | — | [28, 29, 32-34] |
| **Student Transactions**| `/transactions` | [39] | [56] | — | — | — | [46-52, 54, 55, 57] | [43, 44, 49] | [51] | [119] | [48] (AI) |
| **Student Categories** | `/categories` | [57] | [65] | — | — | — | [61-63] | — | [63] | — | — |
| **Student Budgets** | `/budgets` | [66] | [72] | — | — | — | [69-71] | [67, 70] | [71] | [68] | — |
| **Student Saving Tips**| `/tips` | [73] | [77] | — | — | — | — | — | — | [74, 75] | — |
| **Student Reports** | `/reports` | [78] | [86] | — | — | — | — | [84] | — | — | [80-83, 85] |
| **Admin Shell** | `/admin/*` | [87] | [89] | — | [88] | — | — | [114] | — | — | [123] (Sync) |
| **Admin Telemetry** | `/admin/dashboard`| [90] | [92] | — | [91] | — | — | — | — | — | [123] |
| **Admin Students** | `/admin/users` | [93] | [97] | — | — | — | [96] | [95] | — | [119] | — |
| **Admin Categories** | `/admin/categories`| [98-100] | [104] | — | — | — | [101, 102] | — | [103] | — | [116, 117] |

*Legend: Bracketed numbers refer to the numeric prefix of screenshot files in [`docs/ui-screenshots/`](file:///d:/CodingWizard/Projects/CampusCoin/docs/ui-screenshots).*

---

## 2. Component Design System Coverage Matrix

| Component Category | Target Element / Pattern | Default / Rest | Focused / Active | Filled / Valid | Error / Invalid | Destructive / Notice | Mobile Adapted | Screenshot File |
| :--- | :--- | :---: | :---: | :---: | :---: | :---: | :---: | :--- |
| **Buttons** | Primary & Secondary Actions | ✓ | ✓ | — | — | — | — | `components/buttons/105-buttons-primary-secondary.png` |
| **Buttons** | Destructive & Disabled Protection| — | — | — | ✓ | ✓ | — | `components/buttons/106-buttons-destructive-disabled.png` |
| **Buttons** | Tactile Icon Buttons (`btn-icon`) | ✓ | ✓ | — | — | — | — | `components/buttons/107-buttons-icon-actions.png` |
| **Forms** | Input State Progression | ✓ | ✓ | ✓ | — | — | — | `components/forms/108-inputs-default-focused-filled.png` |
| **Forms** | Field Validation Alerts | — | — | — | ✓ | — | — | `components/forms/109-inputs-validation-error-state.png` |
| **Forms** | Native Select Dropdowns | ✓ | ✓ | — | — | — | — | `components/forms/110-selects-closed-and-open.png` |
| **Forms** | Segmented Bar Selection Toggles | ✓ | ✓ | — | — | — | — | `components/forms/111-toggles-segmented-bars.png` |
| **Modals** | Desktop Surface & Contact Shadow | ✓ | — | — | — | — | — | `components/modals/112-modal-surface-geometry-shadow.png` |
| **Modals** | Mobile Bottom Sheet Modal | — | — | — | — | — | ✓ | `components/modals/113-modal-bottom-sheet-mobile.png` |
| **Dropdowns** | Accessibility Font Scaling Menu | ✓ | ✓ | ✓ | — | — | — | `components/dropdowns/114-dropdown-font-scaling-menu.png` |
| **Dropdowns** | Filter Select Popovers | ✓ | ✓ | — | — | — | — | `components/dropdowns/115-dropdown-category-select-options.png` |
| **Toasts** | Livewire Polite Status Feedback | ✓ | — | — | — | — | — | `components/toasts/116-toasts-success-feedback.png` |
| **Toasts** | Livewire Assertive Danger Alert | — | — | — | ✓ | ✓ | — | `components/toasts/117-toasts-error-danger-alert.png` |
| **Toasts** | Gold Warning / Baseline Notice | — | — | — | — | ✓ | — | `components/toasts/118-toasts-warning-budget-notice.png` |
| **States** | Zero-State Data Fallback Banner | ✓ | — | — | — | — | — | `components/states/119-states-empty-ledger-or-search.png` |
| **States** | WCAG 2.2 `:focus-visible` Ring | — | ✓ | — | — | — | — | `components/states/120-states-keyboard-focus-visible.png` |
| **States** | Skip Navigation Anchor Link | — | ✓ | — | — | — | — | `components/states/121-states-skip-to-content.png` |
| **States** | Semantic Status Badges | ✓ | — | — | — | — | — | `components/states/122-states-status-badges-collection.png` |
| **States** | Live Pulse Synchronization Pill | ✓ | — | — | — | — | — | `components/states/123-states-loading-indicator.png` |

---

## 3. Detailed State Reproducibility Analysis

In accordance with strict audit requirements, all captured states were verified against real application behavior and database integrity constraints:

### A. Reproducible States (Captured)
1. **Validation Failures**: Captured by submitting empty or malformed inputs through standard client interactions without corrupting server session integrity (e.g., student registration missing cohort, admin category missing name, invalid login credentials).
2. **Referential Integrity Safety Barrier**: Captured by attempting to delete a system default category (`Food`) that has active transaction bindings; the application code intercepts the action and outputs a safety error alert (`103-admin-categories-delete-barrier-alert.png`).
3. **Empty Data States**: Naturally occurring on unpopulated features (e.g. Budgets zero-state `68-budgets-empty-state.png`, Pinned Tips empty tab `74-tips-pinned-tab-empty.png`) and via filter mismatches (`119-states-empty-ledger-or-search.png`).
4. **Interactive Overlays**: All modal dialogs (Add Transaction, Edit Transaction, CSV Import, Custom Category, Budget Goal, User Inspection) and popover menus (Font scaling, mobile navigation drawer) were opened and visually verified.
5. **Theme & Scaling**: Both Warm Ivory Light Mode and Deep Olive Night Dark Mode, as well as 100% Normal, 112.5% Large, and 125% X-Large CSS root scalings, were applied and documented.

### B. Intentionally Non-Reproduced States
- **Database Connection Collapse / 500 Fatal Error Screens**: Not captured — triggering these states requires terminating the database daemon or tampering with database configuration, which would disrupt application availability and violate data preservation rules.
- **Permanent Data Deletions**: Destructive actions were halted after capturing confirmation dialogs and rejection alerts to ensure no user records, categories, or transactions were lost.
