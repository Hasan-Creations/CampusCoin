# Campus Coin — Architectural & Implementation Decisions

## DEC-001 — Technology Stack Selection
- **Decision:** Laravel 12 + Blade + Livewire 3 + MySQL / MariaDB.
- **Reason:** Fast, robust, and secure server-rendered development for a data-dense financial application. Avoids unnecessary SPA/API build overhead while providing dynamic real-time reactivity where needed.
- **Status:** Accepted

---

## DEC-002 — Monetary Representation and Precision
- **Decision:** All monetary data stored as `DECIMAL(10, 2)` in MySQL, and handled via string/BCMath calculations in PHP.
- **Reason:** Floating-point data types (`float`, `double`) introduce catastrophic precision errors in ledger addition and balance reconciliations.
- **Status:** Accepted

---

## DEC-003 — Authentication & Role Architecture
- **Decision:** Unified `users` table with indexed `role` (`student`, `admin`) and `status` (`active`, `disabled`) columns. Dedicated route groups with middleware enforcement (`EnsureUserIsAdmin`).
- **Reason:** Simplifies identity management while enforcing strong separation of privileges. Prevents privilege escalation and enables administrators to deactivate compromised or abusive accounts.
- **Status:** Accepted

---

## DEC-004 — Student Data Isolation Boundary
- **Decision:** Student financial tables (`transactions`, `budgets`, `categories`, `insights`) always require non-null `user_id` foreign keys (except system default categories where `user_id` is null). Queries enforce `Auth::id()` server-side.
- **Reason:** Complete tenant isolation is mandatory. One student must never access, view, or mutate another student's financial records.
- **Status:** Accepted

---

## DEC-005 — Design Language & Strict Anti-Cliché Rules
- **Decision:** Precision fintech aesthetic inspired by Linear, Stripe, and Copilot Money. Space Grotesk for headings, Inter for UI, JetBrains Mono for figures. Strict prohibition of pill buttons, gradients, glassmorphism, and emoji UI icons.
- **Reason:** Delivers a focused, professional, high-density tool tailored for student financial control without generic SaaS tropes.
- **Status:** Accepted

---

## DEC-006 — AI Advisor Boundary & Graceful Degradation
- **Decision:** AI categorization and monthly insights are completely advisory and encapsulated in service classes. Application logic functions identically when AI services are offline.
- **Reason:** Students must retain complete control over their category assignments and budgets. External AI outages must never block expense logging.
- **Status:** Accepted

---

## DEC-007 — Deterministic Saving Tips Engine & Composite Key State Persistence
- **Decision:** Build the Saving Tips Engine (`SavingTipsService`) on deterministic, database-driven rules (above-average spending, approaching/exceeded category budgets, single-category concentration >40%, month-over-month growth >25%, and savings goal lagging) rather than relying on LLM generation. Calculate potential savings with BCMath and order tips deterministically by savings impact. Persist tips into a `saving_tips` table with a composite unique index on `(user_id, rule_key, category_id)`.
- **Reason:** Deterministic evaluation guarantees 100% predictable, explainable, and reproducible financial recommendations with zero network latency, zero token costs, and no hallucination risk. The unique composite index enables idempotent re-evaluation (`syncTips`) where live numbers update and resolved tips expire, while user preferences (`pinned`, `dismissed`) remain permanently intact.
- **Status:** Accepted

---

## DEC-008 — Advisory AI Categorization, Heuristic Fallback & Student Learning Layer
- **Decision:** Implement a dual-provider architecture behind `CategorizationProviderInterface` with `HeuristicCategorizationProvider` (zero-latency semantic matching across student domains) and `OpenAiCategorizationProvider` (optional LLM integration with 3-second timeout). Precede all provider calls with student-isolated `CategoryLearning` lookups and strictly validate all suggestions against the student's available categories before rendering.
- **Reason:** Ensures zero-cost, zero-latency instant suggestions without requiring external API keys while providing clean extensibility when keys are provided. Strictly prevents category hallucinations, protects student privacy, preserves manual override supremacy, and enables customized suggestions based on previous student choices.
- **Status:** Accepted

---

## DEC-009 — Operational Admin Governance, Non-Destructive Category Archival & Session Termination
- **Decision:** Implement dedicated operational admin controllers and Livewire components (`/admin/dashboard`, `/admin/categories`, `/admin/users`) protected by `EnsureUserIsAdmin` and component-level lifecycle guards. Extend `categories` with an indexed `is_active` boolean column allowing administrators to deactivate global or custom categories without deleting records. Strictly block hard deletion of categories referenced by transactions, budgets, saving tips, or learned mappings via `canBeSafelyDeleted()`. Append `EnsureUserIsActive` to the global `web` middleware pipeline, and purge active database session records from the `sessions` table immediately upon account deactivation.
- **Reason:** Hard deletion of referenced categories would violate foreign-key constraints and corrupt historical transaction audits and monthly reports. Category deactivation gracefully hides obsolete categories from student creation dropdowns while maintaining 100% financial integrity. Immediate database session invalidation ensures disabled students cannot continue accessing protected endpoints through lingering cookies or concurrent tabs.
- **Status:** Accepted

---

## DEC-010 — Accessibility Architecture, Root Font Scaling & FOUC Prevention
- **Decision:** Implement an immediate synchronous boot script in `<head>` that parses `theme` and `font-size` from `localStorage` before any stylesheets or DOM elements render, eliminating Flash of Unstyled Content (FOUC) and layout jump. Scale typography globally using root `html[data-font-size="..."]` CSS percentages (`normal` 100%, `large` 112.5%, `xlarge` 125%) per SRS §1.6 & §185 rather than altering component-level pixel definitions. Implement high-contrast `:focus-visible` indicators, semantic ARIA dialog attributes on all modals, `<th scope="col">` column scopes on all tables, and `@media (prefers-reduced-motion: reduce)` motion dampening.
- **Reason:** Root-level font percentage scaling allows every `rem`-based font, spacing, and component dimension to adjust proportionally without breaking responsive flex layouts or truncating text in fixed-height cards. The synchronous `<head>` script eliminates distracting visual flashes during navigation, while high-contrast focus rings and complete ARIA attributes satisfy WCAG 2.1 AA standards for keyboard users and assistive technologies.
- **Status:** Accepted

