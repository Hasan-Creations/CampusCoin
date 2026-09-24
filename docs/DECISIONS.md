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

