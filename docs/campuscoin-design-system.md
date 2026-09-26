# CampusCoin Design System — "Ledger" Concept (v2 — Old Money Green)

**v2 supersedes v1.** Palette changed from the gold/ledger-green set to
"Old Money Green." Radius and button-hover behavior are also revised —
this version is authoritative, do not mix v1 and v2 tokens.

Reference mockups:
- Layout/composition reference (v1 palette, superseded — structure still valid): https://claude.ai/artifact/UQoBrewpeFF43E7MeLkmTi
- Palette comparison set (Old Money Green is #3): https://claude.ai/artifact/XzqJJdhUtYe7wJSiStcCY8

Concept: CampusCoin is a student cashbook, not a generic fintech dashboard. Every
visual decision below should read as "leather-bound ledger / bank passbook /
receipt," not "SaaS card kit." When in doubt, ask: would this appear in a
physical ledger? If not, cut it.

Do not deviate from these tokens per-screen. Every screen pulls from the same
source (Tailwind config below) so the app reads as one product across all
~50+ screens, not a redesigned dashboard bolted onto old screens.

---

## 1. Color — "Old Money Green" (v2, approved)

| Token | Hex | Role |
|---|---|---|
| `paper` | `#F8F7F3` | Base background (light mode) |
| `panel` | `#FFFFFF` | Card/panel surface |
| `ink` | `#221F19` | Body text, masthead bg, primary button fill |
| `accent` | `#234F3B` | Forest green — primary CTA, income/positive values, brand mark |
| `secondary` | `#B08C4F` | Bronze — secondary actions, savings/goal highlights |
| `expense` | `#A8431F` | Rust — **semantic only**: expenses, destructive actions, errors. Never decorative, never a brand substitute. |
| `hairline` | `#DAD8CC` | All borders and rules |
| `muted` | `#8F8D80` | Secondary/label text (small-caps labels, meta) |

Only two colors carry brand meaning: `accent` (forest green) and `secondary`
(bronze). `expense` is a status color, not a third brand color — it never
appears in the logo, headlines, or decorative rules.

### Dark mode (swap, don't invent new colors)

| Token | Light | Dark |
|---|---|---|
| `paper` | `#F8F7F3` | `#15140F` |
| `panel` | `#FFFFFF` | `#221F19` |
| `ink` (text) | `#221F19` | `#F1EFE6` |
| `hairline` | `#DAD8CC` | `#3A362C` |
| `accent` | `#234F3B` | `#4E8A70` |
| `secondary` | `#B08C4F` | `#CDA968` |
| `expense` | `#A8431F` | `#D2704A` |

### Contrast rules (enforced, not optional)
- Body/UI text: `ink` on `paper`/`panel` only (~15:1).
- `accent` / `secondary` / `expense`: safe for large mono figures, buttons,
  and status text at the sizes used in the mockups; verify contrast before
  dropping any of them into small (<14px) body copy.
- Never use `hairline` or `muted` for anything that must pass as primary
  text.

---

## 2. Typography

| Role | Family | Notes |
|---|---|---|
| Display | **Fraunces** (serif) | Headlines, section titles, the hero balance figure. Weight 400–500. |
| UI / body | **IBM Plex Sans** | Everything else — nav, buttons, paragraphs, table text. |
| Numeric / money | **IBM Plex Mono** | **Every dollar amount, always, no exceptions.** Right-align in tables. |
| Labels | IBM Plex Sans, `font-variant: small-caps` | Not tracked ALL-CAPS — small-caps reads editorial, tracked capitals read templated. |

Google Fonts import:
```
Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600
IBM+Plex+Sans:wght@400;500;600
IBM+Plex+Mono:wght@400;500;600
```

---

## 3. Layout & spacing

- Base unit: **8px**. All spacing in multiples of it.
- Panel padding: **32–48px**, never below 24px.
- Max content width: **1280px** desktop.
- Fixed **240px dark sidebar** (`ink` background) on every authenticated
  screen — student and admin both. Must feel identical across the whole app,
  not just the dashboard, or the "campus ID card" identity breaks.
- Mobile breakpoint ~640px: ledger tables collapse to stacked cards (strict
  row tables break on narrow screens).

## 4. Shape

- Border radius: **`0` everywhere, no exceptions**, on every piece of UI
  chrome — buttons, inputs, cards, panels, tables, modals, dropdowns,
  badges, toasts. This was `0–2px` in v1; v2 tightens it to a hard `0`.
- The only circular elements in the app are the avatar photo and status
  dots — that's shape-correctness (a dot is a circle), not "rounded corner"
  softness, and is not an exception to the rule above.
- Buttons: **rectangular, not pills.** Pills read consumer-fintech
  (Revolut/Cleo-style); rectangular reads institutional — that's the point
  of this whole direction.

## 4a. Button interaction — invert on hover (v2, replaces v1 §7 button spec)

Every button, regardless of variant, follows the same pattern: **solid fill
at idle, inverts to an outline of the same color on hover.** No exceptions,
no variant gets a different interaction style — consistency here is what
reads as "one system" rather than "a button someone styled once."

```css
.btn {
  border-radius: 0;
  border: 1px solid transparent;
  padding: 13px 22px;
  font-family: 'IBM Plex Sans', sans-serif;
  font-size: 13.5px;
  letter-spacing: 0.3px;
  cursor: pointer;
  transition: background-color 150ms ease, color 150ms ease, border-color 150ms ease;
}

/* Primary — ink */
.btn-primary        { background: var(--ink);       color: var(--paper);  border-color: var(--ink); }
.btn-primary:hover   { background: var(--paper);     color: var(--ink);    border-color: var(--ink); }

/* Accent — forest green, the one or two CTAs that should read as "the" action */
.btn-accent          { background: var(--accent);    color: var(--paper);  border-color: var(--accent); }
.btn-accent:hover    { background: var(--paper);     color: var(--accent); border-color: var(--accent); }

/* Secondary — bronze, lower-emphasis actions */
.btn-secondary       { background: var(--secondary); color: var(--paper);  border-color: var(--secondary); }
.btn-secondary:hover { background: var(--paper);     color: var(--secondary); border-color: var(--secondary); }

/* Destructive — semantic rust, never decorative */
.btn-destructive       { background: var(--expense); color: var(--paper);  border-color: var(--expense); }
.btn-destructive:hover { background: var(--paper);   color: var(--expense); border-color: var(--expense); }
```

Disabled state: `opacity: 0.4`, no hover behavior, `cursor: not-allowed`.
Focus state (keyboard): `outline: 2px solid var(--accent); outline-offset: 2px`
in addition to whatever hover/idle state it's already in — never remove the
focus outline for visual cleanliness, that's an accessibility regression.

## 4b. Inputs & selects — focus state (v2, new)

Inputs, selects, and textareas follow the same restrained, color-driven
logic as buttons — idle state is neutral, and color only enters on focus,
carried by the accent green rather than a generic browser blue.

```css
.field {
  border-radius: 0;
  border: 1px solid var(--hairline);
  background: var(--panel);
  color: var(--ink);
  padding: 12px 14px;
  font-family: 'IBM Plex Sans', sans-serif;
  font-size: 13.5px;
  transition: border-color 150ms ease;
}

.field:hover   { border-color: var(--muted); }
.field:focus   { border-color: var(--accent); outline: none; }
.field:invalid,
.field.has-error { border-color: var(--expense); }

/* Money-entry fields (transaction amount, budget cap) use mono, right-aligned */
.field-numeric { font-family: 'IBM Plex Mono', monospace; text-align: right; }
```

Select dropdowns use the same `.field` base; the dropdown panel itself
follows §4a's rectangular/zero-radius rule and §5's hairline-border
convention, not a floating rounded card.

## 5. Depth

- Hairline borders (`hairline` token) do the separating by default — but
  "by default" means **real structural boundaries only**: the edge of a
  panel, a divider between table rows, the line under a masthead. A
  hairline border is not a decoration you add to make a section look
  "finished."
- **Do not wrap every heading, card, stat, or block of text in its own
  outline.** If the current implementation has a border around a page
  heading, a border around each stat, and a border around the panel
  containing both, that's three borders doing one job — cut to the one
  that actually separates two different things from each other. A page
  with borders on every element reads as visual noise and undoes the
  whole "restraint" premise of this system; it's the opposite of
  "quietly excellent."
- Rule of thumb before adding a border: name the two distinct regions it's
  separating. If you can't name two things being told apart, it's
  decorative — remove it and let spacing (§3) do the separating instead.
- Shadows are reserved for exactly two things: the hero panel and elevated
  side panels. Buttons, table rows, and nav items stay flat. If everything
  has a shadow, nothing reads as lifted.
- No perforation/dashed-tear decoration — it was tried and cut for reading
  as "cute" rather than "premium." The one decorative device allowed is a
  thin 2px rule in `accent` under major headlines — nothing else gets a
  decorative line.

## 6. Icons

- Thin-stroke outline only, 1.5px weight, one consistent set (Lucide or
  Phosphor, thin variant).
- 20px in nav, 16px in table row actions.
- Never filled/rounded "cutesy" icons, never emoji — including empty states
  and toasts.

## 7. Motion

- Buttons: see §4a — invert fill/outline on hover, 150ms ease. No scale,
  no bounce, no opacity fade (superseded — §4a is authoritative for buttons).
- Table rows: background tint on hover only.
- Modals: fade + 8px upward slide, 200ms ease (no spring/bounce easing).
- The **one** allowed flourish: the hero balance figure counts up on
  dashboard load. Everything else stays static — one orchestrated moment
  beats effects scattered across every element.

## 8. Data visualization

- Flat fills in palette colors only — no gradients on charts. `accent` and
  `secondary` for series fills, `expense` only where a series is genuinely
  showing expenses/negative values.
- Gridlines: hairline weight. Dashed lines only where the dash carries real
  meaning (e.g., a projected/incomplete value) — never as pure decoration.

## 9. Imagery

- No stock photography, no blob illustrations for empty states.
  Typography and real data carry the visual weight.
- Empty states: a dashed-border "ghost" row/card + one direct sentence
  telling the user what to do next. No illustration.

## 10. Voice / copy

- Buttons name the exact action: "Add an entry," not "Submit."
- A button's verb carries through the whole flow — "Publish" produces a
  "Published" toast, not a generic "Success."
- Errors state what happened and how to fix it. No apologizing, no "Oops!"

---

## 11. Tailwind config (starting point)

```js
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      colors: {
        paper: '#F8F7F3',
        panel: '#FFFFFF',
        ink: '#221F19',
        accent: '#234F3B',
        secondary: '#B08C4F',
        expense: '#A8431F',
        hairline: '#DAD8CC',
        muted: '#8F8D80',
        dark: {
          paper: '#15140F',
          panel: '#221F19',
          ink: '#F1EFE6',
          hairline: '#3A362C',
          accent: '#4E8A70',
          secondary: '#CDA968',
          expense: '#D2704A',
        },
      },
      fontFamily: {
        display: ['Fraunces', 'serif'],
        sans: ['IBM Plex Sans', 'sans-serif'],
        mono: ['IBM Plex Mono', 'monospace'],
      },
      borderRadius: {
        DEFAULT: '0px',
        none: '0px',
      },
      boxShadow: {
        panel: '0 20px 46px -30px rgba(21,20,15,0.18)',
        hero: '0 24px 56px -32px rgba(21,20,15,0.20)',
      },
    },
  },
};
```

**Note:** `borderRadius.DEFAULT` is set to `0px` deliberately — this means
Tailwind's `rounded` utility class becomes a no-op. Any Blade/Livewire
markup still using `rounded-md`, `rounded-lg`, `rounded-full` (outside
avatars/status dots) needs those classes removed during implementation,
not just overridden — an agent doing a find-and-replace on the token
values alone will miss literal `rounded-*` utility classes still present
in the markup.

## 12. Shared components to rebuild first

Rebuild these once, in `resources/views/components/`, before touching any
page — every screen that uses them updates for free:

- `x-stat-strip` — the hairline-divided stat row (income/spent/goal/entries)
- `x-ledger-table` — header row (small-caps, muted) + rows (mono amounts,
  right-aligned, hover tint)
- `x-button` — variants: primary (ink), accent (forest green), secondary
  (bronze), destructive (rust). All four follow the identical invert-on-hover
  pattern in §4a — implement this once as the base `.btn` class plus four
  color modifiers, not as four separately-coded components.
- `x-field` — text/number/select inputs, §4b focus behavior. One base class,
  not re-implemented per form.
- `x-modal` — fade + slide-up, hairline border, no radius
- `x-badge` — category/status tags, small-caps
- `x-toast` — success/error/warning, flat fill, no gradient
- `x-empty-state` — dashed border + direct sentence, no illustration

## 13. Rollout priority

1. Tailwind tokens + shared components above (this file, section 11–12)
2. Student dashboard (reference: mockup link at top of this file)
3. Shell/sidebar nav (student + admin — must match exactly)
4. Transactions table (highest-traffic screen)
5. Auth / welcome landing page (first thing judges see)
6. Budgets, Categories
7. Reports
8. Admin screens (dashboard, students, categories)

Do not reskin everything simultaneously. Ship 2–4 in this direction first,
confirm it holds up, then continue down the list.